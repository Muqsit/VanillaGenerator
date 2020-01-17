<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\tree\MegaPineTree;
use muqsit\vanillagenerator\generator\object\tree\MegaSpruceTree;
use muqsit\vanillagenerator\generator\object\tree\RedwoodTree;
use muqsit\vanillagenerator\generator\object\tree\TallRedwoodTree;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\StoneBoulderDecorator;
use muqsit\vanillagenerator\generator\overworld\decorator\types\TreeDecoration;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class MegaTaigaPopulator extends TaigaPopulator{

	/** @var TreeDecoration[] */
	protected static $TREES;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(RedwoodTree::class, 52),
			new TreeDecoration(TallRedwoodTree::class, 26),
			new TreeDecoration(MegaPineTree::class, 36),
			new TreeDecoration(MegaSpruceTree::class, 3)
		];
	}

	public function getBiomes() : ?array{
		return [BiomeIds::REDWOOD_TAIGA, BiomeIds::REDWOOD_TAIGA_HILLS];
	}

	/** @var StoneBoulderDecorator */
	protected $stoneBoulderDecorator;

	public function __construct(){
		parent::__construct();
		$this->stoneBoulderDecorator = new StoneBoulderDecorator();
	}

	protected function initPopulators() : void{
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->tallGrassDecorator->setAmount(7);
		$this->deadBushDecorator->setAmount(0);
		$this->taigaBrownMushroomDecorator->setAmount(3);
		$this->taigaRedMushroomDecorator->setAmount(3);
	}

	protected function populateOnGround(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$this->stoneBoulderDecorator->populate($world, $random, $chunk);
		parent::populateOnGround($world, $random, $chunk);
	}
}

MegaTaigaPopulator::init();