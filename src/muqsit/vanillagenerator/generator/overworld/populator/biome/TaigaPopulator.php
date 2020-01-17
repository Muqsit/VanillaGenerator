<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\tree\RedwoodTree;
use muqsit\vanillagenerator\generator\object\tree\TallRedwoodTree;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\MushroomDecorator;
use muqsit\vanillagenerator\generator\overworld\decorator\types\DoublePlantDecoration;
use muqsit\vanillagenerator\generator\overworld\decorator\types\TreeDecoration;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class TaigaPopulator extends BiomePopulator{

	/** @var DoublePlantDecoration[] */
	protected static $DOUBLE_PLANTS;

	/** @var TreeDecoration[] */
	protected static $TREES;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(VanillaBlocks::LARGE_FERN(), 1)
		];
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(RedwoodTree::class, 2),
			new TreeDecoration(TallRedwoodTree::class, 1)
		];
	}

	/** @var MushroomDecorator */
	protected $taigaBrownMushroomDecorator;

	/** @var MushroomDecorator */
	protected $taigaRedMushroomDecorator;

	public function __construct(){
		$this->taigaBrownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->taigaRedMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
		parent::__construct();
	}

	protected function initPopulators() : void{
		$this->doublePlantDecorator->setAmount(7);
		$this->doublePlantDecorator->setDoublePlants(...self::$DOUBLE_PLANTS);
		$this->treeDecorator->setAmount(10);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->tallGrassDecorator->setFernDensity(0.8);
		$this->deadBushDecorator->setAmount(1);
		$this->taigaBrownMushroomDecorator->setAmount(1);
		$this->taigaBrownMushroomDecorator->setUseFixedHeightRange();
		$this->taigaBrownMushroomDecorator->setDensity(0.25);
		$this->taigaRedMushroomDecorator->setAmount(1);
		$this->taigaRedMushroomDecorator->setDensity(0.125);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::TAIGA, BiomeIds::TAIGA_HILLS, BiomeIds::MUTATED_TAIGA, BiomeIds::TAIGA_COLD, BiomeIds::TAIGA_COLD_HILLS, BiomeIds::MUTATED_TAIGA_COLD];
	}

	protected function populateOnGround(ChunkManager $world, Random $random, Chunk $chunk) : void{
		parent::populateOnGround($world, $random, $chunk);
		$this->taigaBrownMushroomDecorator->populate($world, $random, $chunk);
		$this->taigaRedMushroomDecorator->populate($world, $random, $chunk);
	}
}
TaigaPopulator::init();