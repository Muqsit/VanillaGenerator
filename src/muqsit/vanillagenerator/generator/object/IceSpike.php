<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use function array_key_exists;
use function intdiv;

class IceSpike extends TerrainObject{

	private const MAX_STEM_RADIUS = 1;
	private const MAX_STEM_HEIGHT = 50;

	/** @var int[] */
	private static array $MATERIALS;

	public static function init() : void{
		self::$MATERIALS = [];
		foreach([BlockLegacyIds::AIR, BlockLegacyIds::DIRT, BlockLegacyIds::SNOW_LAYER, BlockLegacyIds::SNOW_BLOCK, BlockLegacyIds::ICE, BlockLegacyIds::PACKED_ICE] as $block_id){
			self::$MATERIALS[$block_id] = $block_id;
		}
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$tip_height = $random->nextBoundedInt(4) + 7;
		$tip_radius = intdiv($tip_height, 4) + $random->nextBoundedInt(2);
		$tip_offset = $random->nextBoundedInt(4);
		if($tip_radius > 1 && $random->nextBoundedInt(60) === 0){
			// sometimes generate a giant spike
			$tip_offset += $random->nextBoundedInt(30) + 10;
		}
		$succeeded = false;
		$stem_radius = max(0, min(self::MAX_STEM_RADIUS, $tip_radius - 1));
		for($x = -$stem_radius; $x <= $stem_radius; ++$x){
			for($z = -$stem_radius; $z <= $stem_radius; ++$z){
				$stackHeight = self::MAX_STEM_HEIGHT;
				if(abs($x) === self::MAX_STEM_RADIUS && abs($z) === self::MAX_STEM_RADIUS){
					$stackHeight = $random->nextBoundedInt(5);
				}
				for($y = $tip_offset - 1; $y >= -3; --$y){
					$block = $world->getBlockAt($source_x + $x, $source_y + $y, $source_z + $z);
					if(array_key_exists($block->getId(), self::$MATERIALS)){
						$world->setBlockAt($source_x + $x, $source_y + $y, $source_z + $z, VanillaBlocks::PACKED_ICE());
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

		for($y = 0; $y < $tip_height; ++$y){
			$f = (1.0 - (float) $y / $tip_height) * $tip_radius;
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
					if(array_key_exists($world->getBlockAt($source_x + $x, $source_y + $tip_offset + $y, $source_z + $z)->getId(), self::$MATERIALS)){
						$world->setBlockAt($source_x + $x, $source_y + $tip_offset + $y, $source_z + $z, VanillaBlocks::PACKED_ICE());
						$succeeded = true;
					}
					if($radius > 1 && $y !== 0){ // same shape in bottom direction
						if(array_key_exists($world->getBlockAt($source_x + $x, $source_y + $tip_offset - $y, $source_z + $z)->getId(), self::$MATERIALS)){
							$world->setBlockAt($source_x + $x, $source_y + $tip_offset - $y, $source_z + $z, VanillaBlocks::PACKED_ICE());
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