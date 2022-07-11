<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\Lake;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class LakeDecorator extends Decorator{

	public function __construct(
		private Block $type,
		private int $rarity,
		private int $base_offset = 0
	){}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		if($random->nextBoundedInt($this->rarity) === 0){
			$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
			$source_y = $random->nextBoundedInt($world->getMaxY() - $this->base_offset) + $this->base_offset;
			if($this->type->getTypeId() === BlockTypeIds::LAVA && ($source_y >= 64 || $random->nextBoundedInt(10) > 0)){
				return;
			}
			while($world->getBlockAt($source_x, $source_y, $source_z)->getTypeId() === BlockTypeIds::AIR && $source_y > 5){
				--$source_y;
			}
			if($source_y >= 5){
				(new Lake($this->type))->generate($world, $random, $source_x, $source_y, $source_z);
			}
		}
	}
}