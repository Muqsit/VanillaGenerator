<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\SugarCane;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class SugarCaneDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = ($chunk->getX() << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunk->getZ() << 4) + $random->nextBoundedInt(16);
		$maxY = $chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f);
		if($maxY <= 0){
			return;
		}
		$sourceY = $random->nextBoundedInt($maxY << 1);
		for($j = 0; $j < 20; ++$j){
			$x = $sourceX + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			$z = $sourceZ + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			(new SugarCane())->generate($world, $random, $x, $sourceY, $z);
		}
	}
}