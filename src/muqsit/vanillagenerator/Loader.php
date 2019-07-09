<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator;

use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use pocketmine\plugin\PluginBase;
use pocketmine\world\generator\GeneratorManager;

class Loader extends PluginBase{

	public function onLoad() : void{
		GeneratorManager::addGenerator(OverworldGenerator::class, "vanilla_overworld");
	}
}