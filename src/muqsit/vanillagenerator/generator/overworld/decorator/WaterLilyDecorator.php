<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class WaterLilyDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_y = $random->nextBoundedInt($chunk->getHighestBlockAt($source_x & Chunk::COORD_MASK, $source_z & Chunk::COORD_MASK) << 1);
		while($world->getBlockAt($source_x, $source_y - 1, $source_z)->getTypeId() === BlockTypeIds::AIR && $source_y > 0){
			--$source_y;
		}

		for($j = 0; $j < 10; ++$j){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if(
				$y >= 0 && $y < World::Y_MAX && $world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR &&
				($down = $world->getBlockAt($x, $y - 1, $z)) instanceof Water && $down->isStill()
			){
				$world->setBlockAt($x, $y, $z, VanillaBlocks::LILY_PAD());
			}
		}
	}
}