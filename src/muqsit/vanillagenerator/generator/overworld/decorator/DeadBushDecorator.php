<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class DeadBushDecorator extends Decorator{

	private const SOIL_TYPES = [BlockTypeIds::SAND, BlockTypeIds::DIRT, BlockTypeIds::HARDENED_CLAY, BlockTypeIds::STAINED_CLAY];

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_y = $random->nextBoundedInt($chunk->getHighestBlockAt($source_x & Chunk::COORD_MASK, $source_z & Chunk::COORD_MASK) << 1);
		while($source_y > 0
			&& ($world->getBlockAt($source_x, $source_y, $source_z)->getTypeId() === BlockTypeIds::AIR
				|| $world->getBlockAt($source_x, $source_y, $source_z) instanceof Leaves)){
			--$source_y;
		}

		for($i = 0; $i < 4; ++$i){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR){
				$block_below = $world->getBlockAt($x, $y - 1, $z)->getTypeId();
				foreach(self::SOIL_TYPES as $soil){
					if($soil === $block_below){
						$world->setBlockAt($x, $y, $z, VanillaBlocks::DEAD_BUSH());
						break;
					}
				}
			}
		}
	}
}