<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class FireDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$amount = 1 + $random->nextBoundedInt(1 + $random->nextBoundedInt(10));
		for($j = 0; $j < $amount; ++$j){
			$sourceX = ($chunk->getX() << 4) + $random->nextBoundedInt(16);
			$sourceZ = ($chunk->getZ() << 4) + $random->nextBoundedInt(16);
			$sourceY = 4 + $random->nextBoundedInt(120);

			for($i = 0; $i < 64; ++$i){
				$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

				$block = $world->getBlockAt($x, $y, $z);
				$blockBelow = $world->getBlockAt($x, $y - 1, $z);
				if(
					$y < 128 &&
					$block->getId() === BlockLegacyIds::AIR &&
					$blockBelow->getId() === BlockLegacyIds::NETHERRACK
				){
					$world->setBlockAt($x, $y, $z, VanillaBlocks::FIRE());
				}
			}
		}
	}
}