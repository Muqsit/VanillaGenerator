<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class Flower extends TerrainObject{

	/** @var Block */
	private Block $block;

	public function __construct(Block $block){
		$this->block = $block;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$succeeded = false;
		$height = $world->getMaxY();
		for($i = 0; $i < 64; ++$i){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			$block = $world->getBlockAt($x, $y, $z);
			if($y < $height && $block->getId() === BlockLegacyIds::AIR && $world->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::GRASS){
				$world->setBlockAt($x, $y, $z, $this->block);
				$succeeded = true;
			}
		}

		return $succeeded;
	}
}