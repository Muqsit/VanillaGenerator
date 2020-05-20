<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\Cactus;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class CactusDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = $chunk->getX() << 4;
		$sourceZ = $chunk->getZ() << 4;
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$sourceY = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) << 1);

		for($l = 0; $l < 10; ++$l){
			$i = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$k = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$j = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			(new Cactus())->generate($world, $random, $x + $i, $j, $z + $k);
		}
	}
}