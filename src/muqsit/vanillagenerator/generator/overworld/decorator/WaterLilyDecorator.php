<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class WaterLilyDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << 4) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << 4) + $random->nextBoundedInt(16);
		$source_y = $random->nextBoundedInt($chunk->getHighestBlockAt($source_x & 0x0f, $source_z & 0x0f) << 1);
		while($world->getBlockAt($source_x, $source_y - 1, $source_z)->getId() === BlockLegacyIds::AIR && $source_y > 0){
			--$source_y;
		}

		for($j = 0; $j < 10; ++$j){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if(
				$y >= 0 && $y < World::Y_MAX && $world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR &&
				$world->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::STILL_WATER
			){
				$world->setBlockAt($x, $y, $z, VanillaBlocks::LILY_PAD());
			}
		}
	}
}