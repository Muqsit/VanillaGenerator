<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\DoublePlant;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

abstract class TerrainObject{

	/**
	 * Plant block types.
	 */
	public const PLANT_TYPES = [
		BlockLegacyIds::TALL_GRASS,
		BlockLegacyIds::YELLOW_FLOWER,
		BlockLegacyIds::RED_FLOWER,
		BlockLegacyIds::DOUBLE_PLANT,
		BlockLegacyIds::BROWN_MUSHROOM,
		BlockLegacyIds::RED_MUSHROOM
	];

	/**
	 * Removes the grass, shrub, flower or mushroom directly above the given block, if present. Does
	 * not drop an item.
	 *
	 * @param ChunkManager $world
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return bool whether a plant was removed; false if none was present
	 */
	public static function killPlantAbove(ChunkManager $world, int $x, int $y, int $z) : bool{
		$blockAbove = $world->getBlockAt($x, $y + 1, $z);
		$mat = $blockAbove->getId();
		if(in_array($mat, static::PLANT_TYPES, true)){
			if(($mat === BlockLegacyIds::DOUBLE_PLANT) && $blockAbove instanceof DoublePlant){
				$dataAbove = $blockAbove->getMeta();
				if(($dataAbove & BlockLegacyMetadata::DOUBLE_PLANT_FLAG_TOP) !== 0){
					$world->setBlockAt($x, $y + 1, $z, BlockFactory::get(BlockLegacyIds::AIR));
				}
			}
			$world->setBlockAt($x, $y, $z, BlockFactory::get(BlockLegacyIds::AIR));
			return true;
		}

		return false;
	}

	/**
	 * Generates this feature.
	 *
	 * @param ChunkManager $world the world to generate in
	 * @param Random $random the PRNG that will choose the size and a few details of the shape
	 * @param int $sourceX the base X coordinate
	 * @param int $sourceY the base Y coordinate
	 * @param int $sourceZ the base Z coordinate
	 * @return bool if successfully generated
	 */
	abstract public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool;
}