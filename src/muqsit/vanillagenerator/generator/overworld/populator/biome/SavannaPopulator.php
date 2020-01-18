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

	/** @var DoublePlantDecoration */
	protected static $DOUBLE_PLANTS;

	/** @var TreeDecoration */
	protected static $TREES;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(VanillaBlocks::TALL_GRASS(), 1)
		];
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(AcaciaTree::class, 4),
			new TreeDecoration(GenericTree::class, 4)
		];
	}

	protected function initPopulators() : void{
		$this->doublePlantDecorator->setAmount(7);
		$this->doublePlantDecorator->setDoublePlants(...self::$DOUBLE_PLANTS);
		$this->treeDecorator->setAmount(1);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->flowerDecorator->setAmount(4);
		$this->tallGrassDecorator->setAmount(20);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::SAVANNA, BiomeIds::SAVANNA_ROCK];
	}
}
SavannaPopulator::init();