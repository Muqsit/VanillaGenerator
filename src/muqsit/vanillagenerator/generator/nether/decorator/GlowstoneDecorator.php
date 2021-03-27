<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class GlowstoneDecorator extends Decorator{

	private const SIDES = [Facing::EAST, Facing::WEST, Facing::DOWN, Facing::UP, Facing::SOUTH, Facing::NORTH];

	/** @var bool */
	private bool $variable_amount;

	public function __construct(bool $variable_amount = false){
		$this->variable_amount = $variable_amount;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$amount = $this->variable_amount ? 1 + $random->nextBoundedInt(1 + $random->nextBoundedInt(10)) : 10;

		$height = $world->getMaxY();
		$source_y_margin = 8 * ($height >> 7);

		for($i = 0; $i < $amount; ++$i){
			$source_x = ($chunk_x << 4) + $random->nextBoundedInt(16);
			$source_z = ($chunk_z << 4) + $random->nextBoundedInt(16);
			$source_y = 4 + $random->nextBoundedInt($height - $source_y_margin);

			$block = $world->getBlockAt($source_x, $source_y, $source_z);
			if(
				$block->getId() !== BlockLegacyIds::AIR ||
				$world->getBlockAt($source_x, $source_y + 1, $source_z)->getId() !== BlockLegacyIds::NETHERRACK
			){
				continue;
			}

			$world->setBlockAt($source_x, $source_y, $source_z, VanillaBlocks::GLOWSTONE());

			for($j = 0; $j < 1500; ++$j){
				$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $source_y - $random->nextBoundedInt(12);
				$block = $world->getBlockAt($x, $y, $z);
				if($block->getId() !== BlockLegacyIds::AIR){
					continue;
				}

				$glowstone_block_count = 0;
				$vector = new Vector3($x, $y, $z);
				foreach(self::SIDES as $face){
					$pos = $vector->getSide($face);
					if($world->getBlockAt($pos->x, $pos->y, $pos->z)->getId() === BlockLegacyIds::GLOWSTONE){
						++$glowstone_block_count;
					}
				}

				if($glowstone_block_count === 1){
					$world->setBlockAt($x, $y, $z, VanillaBlocks::GLOWSTONE());
				}
			}
		}
	}
}