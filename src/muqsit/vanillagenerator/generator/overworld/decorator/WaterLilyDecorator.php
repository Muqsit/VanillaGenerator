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

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
		$sourceY = $random->nextBoundedInt($chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f) << 1);
		while($world->getBlockAt($sourceX, $sourceY - 1, $sourceZ)->getId() === BlockLegacyIds::AIR && $sourceY > 0){
			--$sourceY;
		}

		for($j = 0; $j < 10; ++$j){
			$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if(
				$y >= 0 && $y < World::Y_MAX && $world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR &&
				$world->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::STILL_WATER
			){
				$world->setBlockAt($x, $y, $z, VanillaBlocks::LILY_PAD());
			}
		}
	}
}