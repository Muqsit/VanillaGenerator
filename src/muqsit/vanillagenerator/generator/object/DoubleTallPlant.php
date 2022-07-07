<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\DoublePlant;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class DoubleTallPlant extends TerrainObject{

	public function __construct(
		private DoublePlant $species
	){}

	/**
	 * Generates up to 64 plants around the given point.
	 *
	 * @param ChunkManager $world
	 * @param Random $random
	 * @param int $source_x
	 * @param int $source_y
	 * @param int $source_z
	 * @return bool true whether least one plant was successfully generated
	 */
	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$placed = false;
		$height = $world->getMaxY();
		for($i = 0; $i < 64; ++$i){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			$block = $world->getBlockAt($x, $y, $z);
			$top_block = $world->getBlockAt($x, $y + 1, $z);
			if($y < $height && $block->getTypeId() === BlockTypeIds::AIR && $top_block->getTypeId() === BlockTypeIds::AIR && $world->getBlockAt($x, $y - 1, $z)->getTypeId() === BlockTypeIds::GRASS){
				$world->setBlockAt($x, $y, $z, $this->species->setTop(false));
				$world->setBlockAt($x, $y + 1, $z, $this->species->setTop(true));
				$placed = true;
			}
		}

		return $placed;
	}
}