<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\TallGrass;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class TallGrassDecorator extends Decorator{

	/** @var float */
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
		$species = BlockLegacyMetadata::TALLGRASS_NORMAL;
		if($this->fern_density > 0 && $random->nextFloat() < $this->fern_density){
			$species = BlockLegacyMetadata::TALLGRASS_FERN;
		}
		(new TallGrass(BlockFactory::getInstance()->get(BlockLegacyIds::TALL_GRASS, $species)))->generate($world, $random, ($chunk_x << 4) + $x, $source_y, ($chunk_z << 4) + $z);
	}
}