<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class IceSpike extends TerrainObject{

	private const MAX_STEM_RADIUS = 1;
	private const MAX_STEM_HEIGHT = 50;

	/** @var int[] */
	private static $MATERIALS;

	public static function init() : void{
		self::$MATERIALS = [];
		foreach([BlockLegacyIds::AIR, BlockLegacyIds::DIRT, BlockLegacyIds::SNOW_LAYER, BlockLegacyIds::SNOW_BLOCK, BlockLegacyIds::ICE, BlockLegacyIds::PACKED_ICE] as $block_id){
			self::$MATERIALS[$block_id] = $block_id;
		}
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$tipHeight = $random->nextBoundedInt(4) + 7;
		$tipRadius = (int) ($tipHeight / 4 + $random->nextBoundedInt(2));
		$tipOffset = $random->nextBoundedInt(4);
		if($tipRadius > 1 && $random->nextBoundedInt(60) === 0){
			// sometimes generate a giant spike
			$tipOffset += $random->nextBoundedInt(30) + 10;
		}
		$succeeded = false;
		$stemRadius = max(0, min(self::MAX_STEM_RADIUS, $tipRadius - 1));
		for($x = -$stemRadius; $x <= $stemRadius; ++$x){
			for($z = -$stemRadius; $z <= $stemRadius; ++$z){
				$stackHeight = self::MAX_STEM_HEIGHT;
				if(abs($x) === self::MAX_STEM_RADIUS && abs($z) === self::MAX_STEM_RADIUS){
					$stackHeight = $random->nextBoundedInt(5);
				}
				for($y = $tipOffset - 1; $y >= -3; --$y){
					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					if(array_key_exists($block->getId(), self::$MATERIALS)){
						$world->setBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z, VanillaBlocks::PACKED_ICE());
						--$stackHeight;
						if($stackHeight <= 0){
							$y -= $random->nextBoundedInt(5);
							$stackHeight = $random->nextBoundedInt(5);
						}
					}else{
						break;
					}
				}
			}
		}

		for($y = 0; $y < $tipHeight; ++$y){
			$f = (1.0 - (float) $y / $tipHeight) * $tipRadius;
			$radius = (int) ceil($f);
			for($x = -$radius; $x <= $radius; ++$x){
				$fx = -0.25 - $x;
				for($z = -$radius; $z <= $radius; ++$z){
					$fz = -0.25 - $z;
					if(($x !== 0 || $z !== 0) && ($fx * $fx + $fz * $fz > $f * $f || (
								($x === abs($radius) || $z === abs($radius))
								&& $random->nextFloat() > 0.75))){
						continue;
					}
					// tip shape in top direction
					if(array_key_exists($world->getBlockAt($sourceX + $x, $sourceY + $tipOffset + $y, $sourceZ + $z)->getId(), self::$MATERIALS)){
						$world->setBlockAt($sourceX + $x, $sourceY + $tipOffset + $y, $sourceZ + $z, VanillaBlocks::PACKED_ICE());
						$succeeded = true;
					}
					if($radius > 1 && $y !== 0){ // same shape in bottom direction
						if(array_key_exists($world->getBlockAt($sourceX + $x, $sourceY + $tipOffset - $y, $sourceZ + $z)->getId(), self::$MATERIALS)){
							$world->setBlockAt($sourceX + $x, $sourceY + $tipOffset - $y, $sourceZ + $z, VanillaBlocks::PACKED_ICE());
							$succeeded = true;
						}
					}
				}
			}
		}
		return $succeeded;
	}
}

IceSpike::init();