<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator;

use muqsit\vanillagenerator\generator\nether\NetherGenerator;
use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;
use pocketmine\world\generator\GeneratorManager;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use function fclose;
use function file_exists;
use function is_resource;
use function proc_close;
use function proc_open;
use function proc_terminate;
use function rtrim;
use function stream_get_contents;
use function stream_set_timeout;
use function strlen;
use function unpack;

final class Loader extends PluginBase{

	public const int ACCELERATOR_PROTOCOL = 1;

	/** @var resource|null */
	private $accelerator = null;

	/** @var resource|null */
	private $accelerator_stdin = null;

	public function onLoad() : void{
		$generator_manager = GeneratorManager::getInstance();
		$generator_manager->addGenerator(NetherGenerator::class, "vanilla_nether", fn() => null);
		$generator_manager->addGenerator(OverworldGenerator::class, "vanilla_overworld", fn() => null);
	}

	protected function onEnable() : void{
		$executable_path = Path::join($this->getDataFolder(), Utils::getOS() === Utils::OS_WINDOWS ? "noise-accelerator.exe" : "noise-accelerator");
		if(!file_exists($executable_path)){
			$this->getLogger()->warning("Executable not found at: {$executable_path}");
			$this->getLogger()->warning("The plugin will compute noise in native PHP, which is several times slower.");
			return;
		}

		$process = proc_open([$executable_path], [0 => ["pipe", "r"], 1 => ["pipe", "w"]], $pipes);
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
			throw $e;
		}
		fclose($pipes[1]);

		$this->accelerator = $process;
		$this->accelerator_stdin = $pipes[0];
		$endpoint = rtrim($endpoint, "\r\n");
		NoiseAccelerator::$endpoint = $endpoint;
		$pool = $this->getServer()->getAsyncPool();
		$pool->addWorkerStartHook(function(int $worker) use($pool, $endpoint) : void{
			$pool->submitTaskToWorker(new class($endpoint) extends AsyncTask{
				public function __construct(private string $endpoint){}
				public function onRun() : void{ NoiseAccelerator::$endpoint = $this->endpoint; }
			}, $worker);
		});
		$this->getLogger()->debug("Noise accelerator is running on {$endpoint}");
	}

	protected function onDisable() : void{
		if(is_resource($this->accelerator_stdin)){
			fclose($this->accelerator_stdin);
		}
		$this->accelerator_stdin = null;
		if(is_resource($this->accelerator)){
			proc_terminate($this->accelerator);
			proc_close($this->accelerator);
		}
		$this->accelerator = null;
	}
}
