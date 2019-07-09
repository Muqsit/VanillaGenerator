<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\noise\bukkit\OctaveGenerator;
use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use muqsit\vanillagenerator\generator\object\DoubleTallPlant;
use muqsit\vanillagenerator\generator\object\Flower;
use muqsit\vanillagenerator\generator\object\TallGrass;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class PlainsPopulator extends BiomePopulator{

	/** @var Block[] */
	protected static $PLAINS_FLOWERS;

	/** @var Block[] */
	protected static $PLAINS_TULIPS;

	public static function init() : void{
		self::$PLAINS_FLOWERS = [
			BlockFactory::get(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_POPPY),
			BlockFactory::get(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_AZURE_BLUET),
			BlockFactory::get(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_OXEYE_DAISY),
		];

		self::$PLAINS_TULIPS = [
			BlockFactory::get(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_RED_TULIP),
			BlockFactory::get(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_ORANGE_TULIP),
			BlockFactory::get(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_WHITE_TULIP),
			BlockFactory::get(BlockLegacyIds::RED_FLOWER, BlockLegacyMetadata::FLOWER_PINK_TULIP)
		];
	}

	/** @var OctaveGenerator */
	private $noiseGen;

	/**
	 * Creates a populator specialized for plains.
	 */
	public function __construct(){
		parent::__construct();
		$this->flowerDecorator->setAmount(0);
		$this->tallGrassDecorator->setAmount(0);
		$this->noiseGen = SimplexOctaveGenerator::fromRandomAndOctaves(new Random(2345), 1, 0, 0, 0);
		$this->noiseGen->setScale(1 / 200.0);
	}

	public function getBiomes() : array{
		return [BiomeIds::PLAINS];
	}

	public function populateOnGround(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = $chunk->getX() << 4;
		$sourceZ = $chunk->getZ() << 4;

		$flowerAmount = 15;
		$tallGrassAmount = 5;
		if($this->noiseGen->noise($sourceX + 8, $sourceZ + 8, 0, 0.5, 2.0, false) >= -0.8){
			$flowerAmount = 4;
			$tallGrassAmount = 10;
			for($i = 0; $i < 7; ++$i){
				$x = $sourceX + $random->nextBoundedInt(16);
				$z = $sourceZ + $random->nextBoundedInt(16);
				$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) + 32);
				(new DoubleTallPlant(BlockFactory::get(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_TALLGRASS)))->generate($world, $random, $x, $y, $z);
			}
		}

		if($this->noiseGen->noise($sourceX + 8, $sourceZ + 8, 0, 0.5, 2.0, false) < -0.8){
			$flower = self::$PLAINS_TULIPS[$random->nextBoundedInt(count(self::$PLAINS_TULIPS))];
		}elseif($random->nextBoundedInt(3) > 0){
			$flower = self::$PLAINS_FLOWERS[$random->nextBoundedInt(count(self::$PLAINS_FLOWERS))];
		}else{
			$flower = BlockFactory::get(BlockLegacyIds::DANDELION);
		}

		for($i = 0; $i < $flowerAmount; ++$i){
			$x = $sourceX + $random->nextBoundedInt(16);
			$z = $sourceZ + $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) + 32);
			(new Flower($flower))->generate($world, $random, $x, $y, $z);
		}

		for($i = 0; $i < $tallGrassAmount; ++$i){
			$x = $sourceX + $random->nextBoundedInt(16);
			$z = $sourceZ + $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) << 1);
			(new TallGrass(BlockFactory::get(BlockLegacyIds::TALL_GRASS, BlockLegacyMetadata::TALLGRASS_NORMAL)))->generate($world, $random, $x, $y, $z);
		}

		parent::populateOnGround($world, $random, $chunk);
	}
}

PlainsPopulator::init();