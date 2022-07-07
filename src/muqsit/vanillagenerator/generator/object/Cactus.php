<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class Cactus extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	/**
	 * Generates or extends a cactus, if there is space.
	 *
	 * @param ChunkManager $world
	 * @param Random $random
	 * @param int $source_x
	 * @param int $source_y
	 * @param int $source_z
	 * @return bool
	 */
	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($world->getBlockAt($source_x, $source_y, $source_z)->getTypeId() === BlockTypeIds::AIR){
			$height = $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1;
			for($n = $source_y; $n < $source_y + $height; ++$n){
				$vec = new Vector3($source_x, $n, $source_z);
				$type_below = $world->getBlockAt($source_x, $n - 1, $source_z)->getTypeId();
				if(($type_below === BlockTypeIds::SAND || $type_below === BlockTypeIds::CACTUS) && $world->getBlockAt($source_x, $n + 1, $source_z)->getTypeId() === BlockTypeIds::AIR){
					foreach(self::FACES as $face){
						$face = $vec->getSide($face);
						if($world->getBlockAt($face->x, $face->y, $face->z)->isSolid()){
							return $n > $source_y;
						}
					}

					$world->setBlockAt($source_x, $n, $source_z, VanillaBlocks::CACTUS());
				}
			}
		}
		return true;
	}
}