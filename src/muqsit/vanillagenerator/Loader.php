<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator;

use Generator;
use muqsit\vanillagenerator\generator\nether\NetherGenerator;
use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use pocketmine\plugin\PluginBase;
use pocketmine\promise\PromiseResolver;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;
use pocketmine\world\generator\GeneratorManager;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use function chmod;
use function copy;
use function fclose;
use function file_exists;
use function is_resource;
use function php_uname;
use function proc_close;
use function proc_open;
use function proc_terminate;
use function rtrim;
use function stream_get_contents;
use function strlen;
use function strtolower;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function unpack;

/**
 * @phpstan-type TProcessLifespan Generator<null, string, null, void>
 */
final class Loader extends PluginBase{

	public const int ACCELERATOR_PROTOCOL = 10002;

	/** @var TProcessLifespan|null */
	private ?Generator $accelerator = null;

	public function onLoad() : void{
		$generator_manager = GeneratorManager::getInstance();
		$generator_manager->addGenerator(NetherGenerator::class, "vanilla_nether", fn() => null);
		$generator_manager->addGenerator(OverworldGenerator::class, "vanilla_overworld", fn() => null);
	}

	protected function onEnable() : void{
		$resolver = new PromiseResolver();
		$this->deduceAcceleratorExecutable($resolver);
		$resolver->getPromise()->onCompletion(function(string $executable_path) : void{
			$accelerator = $this->setupAccelerator($executable_path);
			try{
				$accelerator->current();
				$this->accelerator = $accelerator;
			}catch(RuntimeException $e){
				$this->getLogger()->warning("Failed to setup noise accelerator: {$e->getMessage()}");
				$this->getLogger()->warning("The plugin will compute noise in native PHP, which is several times slower.");
			}
		}, function() : void{
			$this->getLogger()->warning("A noise accelerator executable was not found.");
			$this->getLogger()->warning("The plugin will compute noise in native PHP, which is several times slower.");
		});
	}

	public function deduceAcceleratorExecutable(PromiseResolver $resolver) : void{
		$os = Utils::getOS();
		$filename = $os === Utils::OS_WINDOWS ? "noise-accelerator.exe" : "noise-accelerator";
		$local_path = Path::join($this->getDataFolder(), $filename);
		if(file_exists($local_path)){
			$this->getLogger()->debug("Using executable in local path: {$local_path}");
			$resolver->resolve($local_path);
			return;
		}
		$arch = match(strtolower(php_uname("m"))){
			"x86_64", "amd64" => "x86_64",
			"aarch64", "arm64" => "aarch64",
			default => null
		};
		$target = match($os){
			Utils::OS_LINUX => $arch !== null ? "{$arch}-unknown-linux-musl" : null,
			Utils::OS_MACOS => $arch !== null ? "{$arch}-apple-darwin" : null,
			Utils::OS_WINDOWS => $arch === "x86_64" ? "x86_64-pc-windows-msvc" : null,
			default => null
		};
		$stored_filename = Path::getFilenameWithoutExtension($filename) . "-{$target}" . Path::getExtension($filename);
		$stored_path = $this->getResourcePath($stored_filename);
		if($target !== null && file_exists($stored_path)){
			$this->getLogger()->debug("Using executable provided by plugin: {$stored_filename}");
			$resolver->resolve($stored_path);
			return;
		}
		$resolver->reject();
	}

	/**
	 * @param string $executable_path
	 * @return TProcessLifespan
	 */
	public function setupAccelerator(string $executable_path) : Generator /* its a php generator - not vanilla generator :3 */{
		// compiled phar files cannot be executed right away so we have to perform gymnastics...
		$path = tempnam(sys_get_temp_dir(), "vg-noise-exec-");
		$path !== false || throw new RuntimeException("Failed to create temporary file");
		copy($executable_path, $path) || throw new RuntimeException("Failed to copy {$executable_path} to {$path}");
		chmod($path, 0755) || throw new RuntimeException("Failed to make {$path} executable");

		$process = proc_open([$path], [0 => ["pipe", "r"], 1 => ["pipe", "w"]], $pipes);
		is_resource($process) || throw new RuntimeException("Cannot start noise accelerator");
		try{
			$header = stream_get_contents($pipes[1], 6);
			$header !== false || throw new RuntimeException("Failed to read noise accelerator header");
			strlen($header) === 6 || throw new RuntimeException("Failed to read noise accelerator header (" . strlen($header) . " != 6)");
			["version" => $version, "len" => $len] = (array) unpack("Vversion/vlen", $header);
			$version === self::ACCELERATOR_PROTOCOL || throw new RuntimeException("Refusing connection due to version mismatch: {$version} (acc) != " . self::ACCELERATOR_PROTOCOL . " (php)");
			$endpoint = stream_get_contents($pipes[1], $len);
			$endpoint !== false || throw new RuntimeException("Failed to read noise accelerator endpoint");
			strlen($endpoint) === $len || throw new RuntimeException("Failed to read noise accelerator endpoint (" . strlen($endpoint) . " != {$len}");
		}catch(RuntimeException $e){
			proc_terminate($process);
			if(is_resource($pipes[0])){
				fclose($pipes[0]);
			}
			if(is_resource($pipes[1])){
				fclose($pipes[1]);
			}
			proc_close($process);
			unlink($path);
			throw $e;
		}
		fclose($pipes[1]);

		$endpoint = rtrim($endpoint, "\r\n");
		$pool = $this->getServer()->getAsyncPool();
		$worker_hook = static function(int $worker) use(&$endpoint, $pool) : void{
			$pool->submitTaskToWorker(new class($endpoint) extends AsyncTask{
				public function __construct(private ?string $endpoint){}
				public function onRun() : void{ NoiseAccelerator::$endpoint = $this->endpoint; }
			}, $worker);
		};
		$this->getServer()->getAsyncPool()->addWorkerStartHook($worker_hook);
		NoiseAccelerator::$endpoint = $endpoint;
		$this->getLogger()->debug("Noise accelerator is running on {$endpoint}");
		try{
			yield $endpoint;
		}finally{
			if(is_resource($pipes[0])){
				fclose($pipes[0]);
			}
			$pipes[0] = null;
			proc_terminate($process);
			proc_close($process);
			$process = null;

			$endpoint = null;
			NoiseAccelerator::$endpoint = null;
			$pool->removeWorkerStartHook($worker_hook);
			foreach($pool->getRunningWorkers() as $id){
				$worker_hook($id);
			}
			unlink($path);
		}
	}

	protected function onDisable() : void{
		$this->accelerator?->next();
		$this->accelerator = null;
	}
}
