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
use function fgets;
use function file_exists;
use function is_resource;
use function proc_close;
use function proc_open;
use function proc_terminate;
use function rtrim;

final class Loader extends PluginBase{

	/** @var resource|null */
	private $accelerator = null;

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

		$process = proc_open([$executable_path], [1 => ["pipe", "w"]], $pipes);
		is_resource($process) || throw new RuntimeException("Cannot start noise accelerator");
		$endpoint = fgets($pipes[1]);
		fclose($pipes[1]);
		if($endpoint === false){
			proc_close($process);
			throw new RuntimeException("Noise accelerator failed to start");
		}

		$this->accelerator = $process;
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
		if(is_resource($this->accelerator)){
			proc_terminate($this->accelerator);
			proc_close($this->accelerator);
		}
	}
}
