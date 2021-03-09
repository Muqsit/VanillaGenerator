<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class DeadBushDecorator extends Decorator{

	private const SOIL_TYPES = [BlockLegacyIds::SAND, BlockLegacyIds::DIRT, BlockLegacyIds::HARDENED_CLAY, BlockLegacyIds::STAINED_CLAY];

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << 4) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << 4) + $random->nextBoundedInt(16);
		$source_y = $random->nextBoundedInt($chunk->getHighestBlockAt($source_x & 0x0f, $source_z & 0x0f) << 1);
		while($source_y > 0
			&& ($world->getBlockAt($source_x, $source_y, $source_z)->getId() === BlockLegacyIds::AIR
				|| $world->getBlockAt($source_x, $source_y, $source_z)->getId() === BlockLegacyIds::LEAVES)){
			--$source_y;
		}

		for($i = 0; $i < 4; ++$i){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if($world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR){
				$block_below = $world->getBlockAt($x, $y - 1, $z)->getId();
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