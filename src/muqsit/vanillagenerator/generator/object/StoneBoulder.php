<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use function array_key_exists;

class StoneBoulder extends TerrainObject{

	/** @var int[] */
	private static array $GROUND_TYPES;

	public static function init() : void{
		self::$GROUND_TYPES = [];
		foreach([BlockTypeIds::GRASS, BlockTypeIds::DIRT, BlockTypeIds::STONE] as $block_id){
			self::$GROUND_TYPES[$block_id] = $block_id;
		}
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$ground_reached = false;
		while($source_y > 3){
			--$source_y;
			$block = $world->getBlockAt($source_x, $source_y, $source_z);
			if($block->getTypeId() === BlockTypeIds::AIR){
				continue;
			}

			if(array_key_exists($block->getTypeId(), self::$GROUND_TYPES)){
				$ground_reached = true;
				++$source_y;
				break;
			}
		}

		if(!$ground_reached || $world->getBlockAt($source_x, $source_y, $source_z)->getTypeId() !== BlockTypeIds::AIR){
			return false;
		}

		for($i = 0; $i < 3; ++$i){
			$radius_x = $random->nextBoundedInt(2);
			$radius_z = $random->nextBoundedInt(2);
			$radius_y = $random->nextBoundedInt(2);
			$f = ($radius_x + $radius_z + $radius_y) * 0.333 + 0.5;
			$fsquared = $f * $f;
			for($x = -$radius_x; $x <= $radius_x; ++$x){
				$xsquared = $x * $x;
				for($z = -$radius_z; $z <= $radius_z; ++$z){
					$zsquared = $z * $z;
					for($y = -$radius_y; $y <= $radius_y; ++$y){
						if($xsquared + $zsquared + $y * $y > $fsquared){
							continue;
						}
						if(!TerrainObject::killWeakBlocksAbove($world, $source_x + $x, $source_y + $y, $source_z + $z)){
							$world->setBlockAt($source_x + $x, $source_y + $y, $source_z + $z, VanillaBlocks::MOSSY_COBBLESTONE());
						}
					}
				}
			}
			$source_x += $random->nextBoundedInt(4) - 1;
			$source_z += $random->nextBoundedInt(4) - 1;
			$source_y -= $random->nextBoundedInt(2);
		}
		return true;
	}
}
StoneBoulder::init();