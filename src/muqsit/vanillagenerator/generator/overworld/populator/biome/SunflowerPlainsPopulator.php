<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\types\DoublePlantDecoration;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;

class SunflowerPlainsPopulator extends PlainsPopulator{

	/** @var DoublePlantDecoration[] */
	private static $DOUBLE_PLANTS;

	public static function init() : void{
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(BlockFactory::get(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_SUNFLOWER), 1)
		];
	}

	protected function initPopulators() : void{
		$this->doublePlantDecorator->setAmount(10);
		$this->doublePlantDecorator->setDoublePlants(...self::$DOUBLE_PLANTS);
	}

	public function getBiomes() : array{
		return [BiomeIds::MUTATED_PLAINS];
	}
}

SunflowerPlainsPopulator::init();