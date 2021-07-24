<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class DesertMountainsPopulator extends DesertPopulator{

	protected function initPopulators() : void{
		$this->water_lake_decorator->setAmount(1);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::DESERT_MUTATED];
	}
}