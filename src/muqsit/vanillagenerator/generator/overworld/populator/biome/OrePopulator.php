<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\OreType;
use muqsit\vanillagenerator\generator\object\OreVein;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class OrePopulator implements Populator{

	/** array[] */
	private $ores = [];

	/**
	 * Creates a populator for dirt, gravel, andesite, diorite, granite; and coal, iron, gold,
	 * redstone, diamond and lapis lazuli ores.
	 */
	public function __construct(){
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::DIRT), 0, 256, 32), 10);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::GRAVEL), 0, 256, 32), 8);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_GRANITE), 0, 80, 32), 10);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_DIORITE), 0, 80, 32), 10);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::STONE, BlockLegacyMetadata::STONE_ANDESITE), 0, 80, 32), 10);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::COAL_ORE), 0, 128, 16), 20);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::IRON_ORE), 0, 64, 8), 20);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::GOLD_ORE), 0, 32, 8), 2);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::REDSTONE_ORE), 0, 16, 7), 8);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::DIAMOND_ORE), 0, 16, 7), 1);
		$this->addOre(new OreType(BlockFactory::get(BlockLegacyIds::LAPIS_ORE), 16, 16, 6), 1);
	}

	private function addOre(OreType $type, int $value) : void{
		$this->ores[] = [$type, $value];
	}

	public function populate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$cx = $chunk->getX() << 4;
		$cz = $chunk->getZ() << 4;

		/**
		 * @var OreType $oreType
		 * @var int $value
		 */
		foreach($this->ores as [$oreType, $value]){
			for($n = 0; $n < $value; ++$n){
				$sourceX = $cx + $random->nextBoundedInt(16);
				$sourceZ = $cz + $random->nextBoundedInt(16);
				$sourceY = $oreType->getRandomHeight($random);

				(new OreVein($oreType))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
			}
		}
	}
}