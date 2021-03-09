<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\math\Facing;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class PumpkinDecorator extends Decorator{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		if($random->nextBoundedInt(32) === 0){
			$source_x = ($chunk_x << 4) + $random->nextBoundedInt(16);
			$source_z = ($chunk_z << 4) + $random->nextBoundedInt(16);
			$source_y = $random->nextBoundedInt($chunk->getHighestBlockAt($source_x & 0x0f, $source_z & 0x0f) << 1);

			$block_factory = BlockFactory::getInstance();

			for($i = 0; $i < 64; ++$i){
				$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

				if($world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR && $world->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::GRASS){
					$world->setBlockAt($x, $y, $z, $block_factory->get(BlockLegacyIds::PUMPKIN, self::FACES[$random->nextBoundedInt(count(self::FACES))]));
				}
			}
		}
	}
}