<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
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
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return bool
	 */
	public function generate(ChunkManager $world, Random $random, int $x, int $y, int $z) : bool{
		if($world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR){
			$height = $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1;
			for($n = $y; $n < $y + $height; ++$n){
				$vec = new Vector3($x, $n, $z);
				$typeBelow = $world->getBlockAt($x, $n - 1, $z)->getId();
				if(($typeBelow === BlockLegacyIds::SAND || $typeBelow === BlockLegacyIds::CACTUS) && $world->getBlockAt($x, $n + 1, $z)->getId() === BlockLegacyIds::AIR){
					foreach(self::FACES as $face){
						$face = $vec->getSide($face);
						if($world->getBlockAt($face->x, $face->y, $face->z)->isSolid()){
							return $n > $y;
						}
					}

					$world->setBlockAt($x, $n, $z, VanillaBlocks::CACTUS());
				}
			}
		}
		return true;
	}
}