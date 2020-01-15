<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\tree\BigOakTree;
use muqsit\vanillagenerator\generator\object\tree\CocoaTree;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\types\TreeDecoration;

class JungleEdgePopulator extends JunglePopulator{

	public static function init() : void{
		parent::init();
		self::$TREES = [
			new TreeDecoration(BigOakTree::class, 10),
			new TreeDecoration(CocoaTree::class, 45)
		];
	}

	protected function initPopulators() : void{
		$this->treeDecorator->setAmount(2);
		$this->treeDecorator->setTrees(...self::$TREES);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::JUNGLE_EDGE, BiomeIds::MUTATED_JUNGLE_EDGE];
	}
}
JungleEdgePopulator::init();