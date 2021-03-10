<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\SugarCane;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class SugarCaneDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << 4) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << 4) + $random->nextBoundedInt(16);
		$max_y = $chunk->getHighestBlockAt($source_x & 0x0f, $source_z & 0x0f);
		if($max_y <= 0){
			return;
		}
		$source_y = $random->nextBoundedInt($max_y << 1);
		for($j = 0; $j < 20; ++$j){
			$x = $source_x + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			$z = $source_z + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);
			(new SugarCane())->generate($world, $random, $x, $source_y, $z);
		}
	}
}