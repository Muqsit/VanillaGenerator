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
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class PlainsPopulator extends BiomePopulator{

	/** @var Block[] */
	protected static $PLAINS_FLOWERS;

	/** @var Block[] */
	protected static $PLAINS_TULIPS;

	public static function init() : void{
		parent::init();

		self::$PLAINS_FLOWERS = [
			VanillaBlocks::POPPY(),
			VanillaBlocks::AZURE_BLUET(),
			VanillaBlocks::OXEYE_DAISY()
		];

		self::$PLAINS_TULIPS = [
			VanillaBlocks::RED_TULIP(),
			VanillaBlocks::ORANGE_TULIP(),
			VanillaBlocks::WHITE_TULIP(),
			VanillaBlocks::PINK_TULIP()
		];
	}

	/** @var OctaveGenerator */
	private $noiseGen;

	public function __construct(){
		parent::__construct();
		$this->noiseGen = SimplexOctaveGenerator::fromRandomAndOctaves(new Random(2345), 1, 0, 0, 0);
		$this->noiseGen->setScale(1 / 200.0);
	}

	protected function initPopulators() : void{
		$this->flowerDecorator->setAmount(0);
		$this->tallGrassDecorator->setAmount(0);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::PLAINS];
	}

	public function populateOnGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = $chunkX << 4;
		$sourceZ = $chunkZ << 4;

		$flowerAmount = 15;
		$tallGrassAmount = 5;
		if($this->noiseGen->noise($sourceX + 8, $sourceZ + 8, 0, 0.5, 2.0, false) >= -0.8){
			$flowerAmount = 4;
			$tallGrassAmount = 10;
			for($i = 0; $i < 7; ++$i){
				$x = $random->nextBoundedInt(16);
				$z = $random->nextBoundedInt(16);
				$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);
				(new DoubleTallPlant(VanillaBlocks::DOUBLE_TALLGRASS()))->generate($world, $random, $sourceX + $x, $y, $sourceZ + $z);
			}
		}

		if($this->noiseGen->noise($sourceX + 8, $sourceZ + 8, 0, 0.5, 2.0, false) < -0.8){
			$flower = self::$PLAINS_TULIPS[$random->nextBoundedInt(count(self::$PLAINS_TULIPS))];
		}elseif($random->nextBoundedInt(3) > 0){
			$flower = self::$PLAINS_FLOWERS[$random->nextBoundedInt(count(self::$PLAINS_FLOWERS))];
		}else{
			$flower = VanillaBlocks::DANDELION();
		}

		for($i = 0; $i < $flowerAmount; ++$i){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);
			(new Flower($flower))->generate($world, $random, $sourceX + $x, $y, $sourceZ + $z);
		}

		for($i = 0; $i < $tallGrassAmount; ++$i){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) << 1);
			(new TallGrass(VanillaBlocks::TALL_GRASS()))->generate($world, $random, $sourceX + $x, $y, $sourceZ + $z);
		}

		parent::populateOnGround($world, $random, $chunkX, $chunkZ, $chunk);
	}
}

PlainsPopulator::init();