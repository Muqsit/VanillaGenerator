<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class DesertPopulator extends BiomePopulator{

	protected function initPopulators() : void{
		$this->waterLakeDecorator->setAmount(0);
		$this->deadBushDecorator->setAmount(2);
		$this->sugarCaneDecorator->setAmount(60);
		$this->cactusDecorator->setAmount(10);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::DESERT, BiomeIds::DESERT_HILLS];
	}
}