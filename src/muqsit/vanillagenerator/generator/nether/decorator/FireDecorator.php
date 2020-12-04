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

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$amount = 1 + $random->nextBoundedInt(1 + $random->nextBoundedInt(10));

		$height = $world->getWorldHeight();
		$sourceYMargin = 8 * ($height >> 7);

		for($j = 0; $j < $amount; ++$j){
			$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
			$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
			$sourceY = 4 + $random->nextBoundedInt($sourceYMargin);

			for($i = 0; $i < 64; ++$i){
				$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

				$block = $world->getBlockAt($x, $y, $z);
				$blockBelow = $world->getBlockAt($x, $y - 1, $z);
				if(
					$y < $height &&
					$block->getId() === BlockLegacyIds::AIR &&
					$blockBelow->getId() === BlockLegacyIds::NETHERRACK
				){
					$world->setBlockAt($x, $y, $z, VanillaBlocks::FIRE());
				}
			}
		}
	}
}