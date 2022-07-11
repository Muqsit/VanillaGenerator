<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function array_key_exists;

class MushroomDecorator extends Decorator{

	/** @var array<BlockTypeIds::*, BlockTypeIds::*> */
	private static array $MATERIALS;

	public static function init() : void{
		self::$MATERIALS = [];
		foreach([BlockTypeIds::NETHERRACK, BlockTypeIds::NETHER_QUARTZ_ORE, BlockTypeIds::SOUL_SAND, BlockTypeIds::GRAVEL] as $block_id){
			self::$MATERIALS[$block_id] = $block_id;
		}
	}

	public function __construct(
		private Block $type
	){}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$height = $world->getMaxY();

		$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_y = $random->nextBoundedInt($height);

		for($i = 0; $i < 64; ++$i){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			$block = $world->getBlockAt($x, $y, $z);
			$block_below = $world->getBlockAt($x, $y - 1, $z);
			if(
				$y < $height &&
				$block->getTypeId() === BlockTypeIds::AIR &&
				array_key_exists($block_below->getTypeId(), self::$MATERIALS)
			){
				$world->setBlockAt($x, $y, $z, $this->type);
			}
		}
	}
}

MushroomDecorator::init();