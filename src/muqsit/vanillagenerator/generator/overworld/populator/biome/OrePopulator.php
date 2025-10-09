<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\object\OreType;
use muqsit\vanillagenerator\generator\object\OreVein;
use muqsit\vanillagenerator\generator\overworld\populator\biome\utils\OreTypeHolder;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class OrePopulator implements Populator{

	/** @var OreTypeHolder[] */
	private array $ores = [];

	/**
	 * Creates a populator for dirt, gravel, andesite, diorite, granite; and coal, iron, gold,
	 * redstone, diamond and lapis lazuli ores with full 1.18+ distribution.
	 */
	public function __construct(){
		// Full 1.18+ ore distributions for world height Y=-64 to Y=319
		
		// Stone variants - distribute throughout most of the world
		$this->addOre(new OreType(VanillaBlocks::DIRT(), -64, 319, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::GRAVEL(), -64, 319, 32), 8);
		$this->addOre(new OreType(VanillaBlocks::GRANITE(), -64, 64, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::DIORITE(), -64, 64, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::ANDESITE(), -64, 64, 32), 10);
		
		// Ores with authentic 1.18+ distributions
		$this->addOre(new OreType(VanillaBlocks::COAL_ORE(), 0, 190, 16), 20);      // Coal: Y=0 to Y=190
		$this->addOre(new OreType(VanillaBlocks::IRON_ORE(), -63, 72, 8), 20);      // Iron: Y=-63 to Y=72  
		$this->addOre(new OreType(VanillaBlocks::GOLD_ORE(), -64, -48, 8), 4);      // Gold: Deep underground Y=-64 to Y=-48
		$this->addOre(new OreType(VanillaBlocks::REDSTONE_ORE(), -64, 15, 7), 8);   // Redstone: Y=-64 to Y=15
		$this->addOre(new OreType(VanillaBlocks::DIAMOND_ORE(), -64, 16, 7), 1);    // Diamond: Y=-64 to Y=16 (peak at Y=-59)
		$this->addOre(new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), -32, 32, 6), 1); // Lapis: Y=-32 to Y=32
	}

	protected function addOre(OreType $type, int $value) : void{
		$this->ores[] = new OreTypeHolder($type, $value);
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$cx = $chunk_x << Chunk::COORD_BIT_SIZE;
		$cz = $chunk_z << Chunk::COORD_BIT_SIZE;

		foreach($this->ores as $ore_type_holder){
			for($n = 0; $n < $ore_type_holder->value; ++$n){
				$source_x = $cx + $random->nextBoundedInt(16);
				$source_z = $cz + $random->nextBoundedInt(16);
				$source_y = $ore_type_holder->type->getRandomHeight($random);
				(new OreVein($ore_type_holder->type))->generate($world, $random, $source_x, $source_y, $source_z);
			}
		}
	}
}