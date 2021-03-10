<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class SavannaMountainsPopulator extends SavannaPopulator{

	protected function initPopulators() : void{
		$this->tree_decorator->setAmount(2);
		$this->flower_decorator->setAmount(2);
		$this->tall_grass_decorator->setAmount(5);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::MUTATED_SAVANNA, BiomeIds::MUTATED_SAVANNA_ROCK];
	}
}