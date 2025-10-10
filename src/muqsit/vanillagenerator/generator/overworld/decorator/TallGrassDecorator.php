<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\TallGrass;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class TallGrassDecorator extends Decorator{

	private float $fern_density = 0.0;

	final public function setFernDensity(float $fern_density) : void{
		$this->fern_density = $fern_density;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$top_block = $chunk->getHighestBlockAt($x, $z);
		if($top_block <= 0){
			// Nothing to do if this column is empty
			return;
		}

		$source_y = $random->nextBoundedInt(abs($top_block << 1));

		// the grass species can change on each decoration pass
		(new TallGrass($this->fern_density > 0 && $random->nextFloat() < $this->fern_density ?
			VanillaBlocks::FERN() :
			VanillaBlocks::TALL_GRASS()
		))->generate($world, $random, ($chunk_x << Chunk::COORD_BIT_SIZE) + $x, $source_y, ($chunk_z << Chunk::COORD_BIT_SIZE) + $z);
	}
}