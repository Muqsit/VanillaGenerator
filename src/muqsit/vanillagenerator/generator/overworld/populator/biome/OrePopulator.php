<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\OreType;
use muqsit\vanillagenerator\generator\object\OreVein;
use muqsit\vanillagenerator\generator\overworld\populator\biome\utils\OreTypeHolder;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\BlockTypeIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class OrePopulator implements Populator {

	/** @var OreTypeHolder[] */
	private array $ores = [];

	/**
	 * Creates a populator for dirt, gravel, andesite, diorite, granite; and coal, iron, gold,
	 * redstone, diamond and lapis lazuli ores with full 1.18+ distribution.
	 */
	public function __construct() {
		// Stone variants - distribute throughout most of the world
		$this->addOre(new OreType(VanillaBlocks::DIRT(), -64, 319, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::GRAVEL(), -64, 319, 32), 8);
		$this->addOre(new OreType(VanillaBlocks::GRANITE(), -64, 64, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::DIORITE(), -64, 64, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::ANDESITE(), -64, 64, 32), 10);
	
		// Coal
		$this->addOre(new OreType(VanillaBlocks::COAL_ORE(), 1, 190, 16), 20);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_COAL_ORE(), -64, 0, 16, BlockTypeIds::DEEPSLATE), 20);
		// Iron
		$this->addOre(new OreType(VanillaBlocks::IRON_ORE(), 1, 72, 8), 20);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_IRON_ORE(), -64, 0, 8, BlockTypeIds::DEEPSLATE), 20);
		// Gold
		$this->addOre(new OreType(VanillaBlocks::GOLD_ORE(), 1, 32, 8), 4);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_GOLD_ORE(), -64, -48, 8, BlockTypeIds::DEEPSLATE), 4);
		// Redstone
		$this->addOre(new OreType(VanillaBlocks::REDSTONE_ORE(), 1, 15, 7), 8);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_REDSTONE_ORE(), -64, 0, 7, BlockTypeIds::DEEPSLATE), 8);
		// Diamond
		$this->addOre(new OreType(VanillaBlocks::DIAMOND_ORE(), 1, 16, 7), 1);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_DIAMOND_ORE(), -64, 0, 7, BlockTypeIds::DEEPSLATE), 1);
		// Lapis
		$this->addOre(new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), 1, 32, 6), 1);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_LAPIS_LAZULI_ORE(), -32, 0, 6, BlockTypeIds::DEEPSLATE), 1);
		// Copper
		$this->addOre(new OreType(VanillaBlocks::COPPER_ORE(), 1, 112, 8), 20);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_COPPER_ORE(), -64, 0, 8, BlockTypeIds::DEEPSLATE), 20);
		// Emerald
		$this->addOre(new OreType(VanillaBlocks::EMERALD_ORE(), 4, 31, 3, BlockTypeIds::STONE), 1);
		$this->addOre(new OreType(VanillaBlocks::DEEPSLATE_EMERALD_ORE(), -64, 0, 3, BlockTypeIds::DEEPSLATE), 1);
	}

	protected function addOre(OreType $type, int $value): void {
		$this->ores[] = new OreTypeHolder($type, $value);
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk): void {
		$cx = $chunk_x << Chunk::COORD_BIT_SIZE;
		$cz = $chunk_z << Chunk::COORD_BIT_SIZE;

		foreach($this->ores as $ore_type_holder) {
			for($n = 0; $n < $ore_type_holder->value; ++$n) {
				$source_x = $cx + $random->nextBoundedInt(16);
				$source_z = $cz + $random->nextBoundedInt(16);
				$source_y = $ore_type_holder->type->getRandomHeight($random);
				(new OreVein($ore_type_holder->type))->generate($world, $random, $source_x, $source_y, $source_z);
			}
		}
	}
}