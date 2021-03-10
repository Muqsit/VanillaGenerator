<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class SugarCane extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($world->getBlockAt($source_x, $source_y, $source_z)->getId() !== BlockLegacyIds::AIR){
			return false;
		}

		$vec = new Vector3($source_x, $source_y - 1, $source_z);
		$adjacent_water = false;
		foreach(self::FACES as $face){
			// needs a directly adjacent water block
			$block_type_v = $vec->getSide($face);
			$block_type = $world->getBlockAt($block_type_v->x, $block_type_v->y, $block_type_v->z)->getId();
			if($block_type === BlockLegacyIds::STILL_WATER || $block_type === BlockLegacyIds::FLOWING_WATER){
				$adjacent_water = true;
				break;
			}
		}
		if(!$adjacent_water){
			return false;
		}
		for($n = 0; $n <= $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1; ++$n){
			$block = $world->getBlockAt($source_x, $source_y + $n - 1, $source_z);
			$block_id = $block->getId();
			if($block_id === BlocKLegacyIds::SUGARCANE_BLOCK
				|| $block_id === BlocKLegacyIds::GRASS
				|| $block_id === BlocKLegacyIds::SAND
				|| ($block_id === BlocKLegacyIds::DIRT && $block->getMeta() === BlockLegacyMetadata::DIRT_NORMAL)
			){
				$cane_block = $world->getBlockAt($source_x, $source_y + $n, $source_z);
				if($cane_block->getId() !== BlockLegacyIds::AIR && $world->getBlockAt($source_x, $source_y + $n + 1, $source_z)->getId() !== BlockLegacyIds::AIR){
					return $n > 0;
				}

				$world->setBlockAt($source_x, $source_y + $n, $source_z, VanillaBlocks::SUGARCANE());
			}
		}
		return true;
	}
}