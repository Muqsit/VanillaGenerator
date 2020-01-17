<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\tree\RedwoodTree;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\types\TreeDecoration;

class IcePlainsPopulator extends BiomePopulator{
	
	/** @var TreeDecoration[] */
	protected static $TREES;
	
	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(RedwoodTree::class, 1)
		];
	}

	public function getBiomes() : ?array{
		return [BiomeIds::ICE_FLATS, BiomeIds::ICE_MOUNTAINS];
	}
	
	protected function initPopulators() : void{
		$this->treeDecorator->setAmount(0);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->flowerDecorator->setAmount(0);
    }
}

IcePlainsPopulator::init();