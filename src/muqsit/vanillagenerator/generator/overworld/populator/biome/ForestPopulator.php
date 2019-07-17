<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\DoubleTallPlant;
use muqsit\vanillagenerator\generator\object\tree\BirchTree;
use muqsit\vanillagenerator\generator\object\tree\GenericTree;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\types\TreeDecoration;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class ForestPopulator extends BiomePopulator{

	private const BIOMES = [BiomeIds::FOREST, BiomeIds::FOREST_HILLS];

	/** @var TreeDecoration[] */
	protected static $TREES;

	/** @var Block[] */
	private static $DOUBLE_PLANTS;

	public static function init() : void{
		self::$TREES = [
			new TreeDecoration(GenericTree::class, 4),
			new TreeDecoration(BirchTree::class, 1)
		];

		self::$DOUBLE_PLANTS = [
			BlockFactory::get(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_LILAC),
			BlockFactory::get(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_ROSE_BUSH),
			BlockFactory::get(BlockLegacyIds::DOUBLE_PLANT, BlockLegacyMetadata::DOUBLE_PLANT_PEONY)
		];
	}

	/** @var int */
	protected $doublePlantLoweringAmount = 3;

	protected function initPopulators() : void{
		$this->doublePlantDecorator->setAmount(0);
		$this->treeDecorator->setAmount(10);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->tallGrassDecorator->setAmount(2);
	}

	public function getBiomes() : array{
		return self::BIOMES;
	}

	public function populateOnGround(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = $chunk->getX() << 4;
		$sourceZ = $chunk->getZ() << 4;
		$amount = $random->nextBoundedInt(5) - $this->doublePlantLoweringAmount;
		$i = 0;
		while($i < $amount){
			for($j = 0; $j < 5; ++$j, ++$i){
				$x = $sourceX + $random->nextBoundedInt(16);
				$z = $sourceZ + $random->nextBoundedInt(16);
				$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) + 32);
				$species = self::$DOUBLE_PLANTS[$random->nextBoundedInt(count(self::$DOUBLE_PLANTS))];
				if((new DoubleTallPlant($species))->generate($world, $random, $x, $y, $z)){
					++$i;
					break;
				}
			}
		}

		parent::populateOnGround($world, $random, $chunk);
	}
}

ForestPopulator::init();