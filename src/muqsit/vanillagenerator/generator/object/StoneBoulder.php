<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class StoneBoulder extends TerrainObject{

	/** @var int[] */
	private static $GROUND_TYPES;

	public static function init() : void{
		self::$GROUND_TYPES = [];
		foreach([BlockLegacyIds::GRASS, BlockLegacyIds::DIRT, BlockLegacyIds::STONE] as $block_id){
			self::$GROUND_TYPES[$block_id] = $block_id;
		}
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$groundReached = false;
		while($sourceY > 3){
			--$sourceY;
			$block = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
			if($block->getId() === BlockLegacyIds::AIR){
				continue;
			}

			if(array_key_exists($block->getId(), self::$GROUND_TYPES)){
				$groundReached = true;
				++$sourceY;
				break;
			}
		}

		if(!$groundReached || $world->getBlockAt($sourceX, $sourceY, $sourceZ)->getId() !== BlockLegacyIds::AIR){
			return false;
		}

		for($i = 0; $i < 3; ++$i){
			$radiusX = $random->nextBoundedInt(2);
			$radiusZ = $random->nextBoundedInt(2);
			$radiusY = $random->nextBoundedInt(2);
			$f = ($radiusX + $radiusZ + $radiusY) * 0.333 + 0.5;
			$fsquared = $f * $f;
			for($x = -$radiusX; $x <= $radiusX; ++$x){
				$xsquared = $x * $x;
				for($z = -$radiusZ; $z <= $radiusZ; ++$z){
					$zsquared = $z * $z;
					for($y = -$radiusY; $y <= $radiusY; ++$y){
						if($xsquared + $zsquared + $y * $y > $fsquared){
							continue;
						}
						if(!TerrainObject::killWeakBlocksAbove($world, $sourceX + $x, $sourceY + $y, $sourceZ + $z)){
							$world->setBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z, VanillaBlocks::MOSSY_COBBLESTONE());
						}
					}
				}
			}
			$sourceX += $random->nextBoundedInt(4) - 1;
			$sourceZ += $random->nextBoundedInt(4) - 1;
			$sourceY -= $random->nextBoundedInt(2);
		}
		return true;
	}
}
StoneBoulder::init();