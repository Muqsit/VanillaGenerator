<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\math\Facing;

final class BlockUtils{

	public static function VINE(int $face) : Block{
		static $meta = [
			Facing::NORTH => BlockLegacyMetadata::VINE_FLAG_NORTH,
			Facing::SOUTH => BlockLegacyMetadata::VINE_FLAG_SOUTH,
			Facing::EAST => BlockLegacyMetadata::VINE_FLAG_EAST,
			Facing::WEST => BlockLegacyMetadata::VINE_FLAG_WEST
		];

		return BlockFactory::get(BlockLegacyIds::VINE, $meta[$face]);
	}

	public static function COCOA(int $face, int $age = 0) : Block{
		return BlockFactory::get(BlockLegacyIds::COCOA, BlockDataSerializer::writeLegacyHorizontalFacing(Facing::opposite($face)) | ($age << 2));
	}
}