<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\Dirt;
use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class SugarCane extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($world->getBlockAt($source_x, $source_y, $source_z)->getTypeId() !== BlockTypeIds::AIR){
			return false;
		}

		$vec = new Vector3($source_x, $source_y - 1, $source_z);
		$adjacent_water = false;
		foreach(self::FACES as $face){
			// needs a directly adjacent water block
			$block_type_v = $vec->getSide($face);
			$block_type = $world->getBlockAt($block_type_v->x, $block_type_v->y, $block_type_v->z);
			if($block_type instanceof Water){
				$adjacent_water = true;
				break;
			}
		}
		if(!$adjacent_water){
			return false;
		}
		for($n = 0; $n <= $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1; ++$n){
			$block = $world->getBlockAt($source_x, $source_y + $n - 1, $source_z);
			$block_id = $block->getTypeId();
			if($block_id === BlockTypeIds::SUGARCANE
				|| $block_id === BlockTypeIds::GRASS
				|| $block_id === BlockTypeIds::SAND
				|| ($block instanceof Dirt && $block->getDirtType()->equals(DirtType::NORMAL))
			){
				$cane_block = $world->getBlockAt($source_x, $source_y + $n, $source_z);
				if($cane_block->getTypeId() !== BlockTypeIds::AIR && $world->getBlockAt($source_x, $source_y + $n + 1, $source_z)->getTypeId() !== BlockTypeIds::AIR){
					return $n > 0;
				}

				$world->setBlockAt($source_x, $source_y + $n, $source_z, VanillaBlocks::SUGARCANE());
			}
		}
		return true;
	}
}