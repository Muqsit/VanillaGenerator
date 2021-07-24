<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\tree\AcaciaTree;
use muqsit\vanillagenerator\generator\object\tree\GenericTree;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\types\DoublePlantDecoration;
use muqsit\vanillagenerator\generator\overworld\decorator\types\TreeDecoration;
use pocketmine\block\VanillaBlocks;

class SavannaPopulator extends BiomePopulator{

	/** @var DoublePlantDecoration[] */
	protected static array $DOUBLE_PLANTS;

	/** @var TreeDecoration[] */
	protected static array $TREES;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(VanillaBlocks::DOUBLE_TALLGRASS(), 1)
		];
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(AcaciaTree::class, 4),
			new TreeDecoration(GenericTree::class, 4)
		];
	}

	protected function initPopulators() : void{
		$this->double_plant_decorator->setAmount(7);
		$this->double_plant_decorator->setDoublePlants(...self::$DOUBLE_PLANTS);
		$this->tree_decorator->setAmount(1);
		$this->tree_decorator->setTrees(...self::$TREES);
		$this->flower_decorator->setAmount(4);
		$this->tall_grass_decorator->setAmount(20);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::SAVANNA, BiomeIds::SAVANNA_PLATEAU];
	}
}
SavannaPopulator::init();