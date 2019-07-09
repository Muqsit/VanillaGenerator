<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class SugarCane extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public function generate(ChunkManager $world, Random $random, int $x, int $y, int $z) : bool{
		if(!$world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR){
			return false;
		}

		$vec = new Vector3($x, $y - 1, $z);
		$adjacentWater = false;
		foreach(self::FACES as $face){
			// needs a directly adjacent water block
			$blockTypeV = $vec->getSide($face);
			$blockType = $world->getBlockAt($blockTypeV->x, $blockTypeV->y, $blockTypeV->z)->getId();
			if($blockType === BlockLegacyIds::STILL_WATER || $blockType === BlockLegacyIds::WATER){
				$adjacentWater = true;
				break;
			}
		}
		if(!$adjacentWater){
			return false;
		}
		for($n = 0; $n <= $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1; ++$n){
			$block = $world->getBlockAt($x, $y + $n - 1, $z);
			$blockId = $block->getId();
			if($blockId === BlocKLegacyIds::SUGARCANE_BLOCK
				|| $blockId === BlocKLegacyIds::GRASS
				|| $blockId === BlocKLegacyIds::SAND
				|| ($blockId === BlocKLegacyIds::DIRT && $block->getMeta() === BlockLegacyMetadata::DIRT_NORMAL)
			){
				$caneBlock = $world->getBlockAt($x, $y + $n, $z);
				if($caneBlock->getId() !== BlockLegacyIds::AIR && $world->getBlockAt($x, $y + $n + 1, $z)->getId() !== BlockLegacyIds::AIR){
					return $n > 0;
				}

				$world->setBlockAt($x, $y, $z, BlockFactory::get(BlockLegacyIds::SUGARCANE_BLOCK));
			}
		}
		return true;
	}
}