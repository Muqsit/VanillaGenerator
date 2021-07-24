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
	protected static array $DOUBLE_PLANTS;

	/** @var TreeDecoration[] */
	protected static array $TREES;

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

	protected MushroomDecorator $taiga_brown_mushroom_decorator;
	protected MushroomDecorator $taiga_red_mushroom_decorator;

	public function __construct(){
		$this->taiga_brown_mushroom_decorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->taiga_red_mushroom_decorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
		parent::__construct();
	}

	protected function initPopulators() : void{
		$this->double_plant_decorator->setAmount(7);
		$this->double_plant_decorator->setDoublePlants(...self::$DOUBLE_PLANTS);
		$this->tree_decorator->setAmount(10);
		$this->tree_decorator->setTrees(...self::$TREES);
		$this->tall_grass_decorator->setFernDensity(0.8);
		$this->dead_bush_decorator->setAmount(1);
		$this->taiga_brown_mushroom_decorator->setAmount(1);
		$this->taiga_brown_mushroom_decorator->setUseFixedHeightRange();
		$this->taiga_brown_mushroom_decorator->setDensity(0.25);
		$this->taiga_red_mushroom_decorator->setAmount(1);
		$this->taiga_red_mushroom_decorator->setDensity(0.125);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::TAIGA, BiomeIds::TAIGA_HILLS, BiomeIds::TAIGA_MUTATED, BiomeIds::COLD_TAIGA, BiomeIds::COLD_TAIGA_HILLS, BiomeIds::COLD_TAIGA_MUTATED];
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		parent::populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
		$this->taiga_brown_mushroom_decorator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		$this->taiga_red_mushroom_decorator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
	}
}
TaigaPopulator::init();