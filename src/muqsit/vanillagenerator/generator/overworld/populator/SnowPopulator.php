<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class SnowPopulator implements Populator{

	public function populate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = $chunk->getX() << 4;
		$sourceZ = $chunk->getZ() << 4;
		for($x = $sourceX; $x < $sourceX + 16; ++$x){
			for($z = $sourceZ; $z < $sourceZ + 16; ++$z){
				$y = $chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) - 1;
				if(BiomeClimateManager::isSnowy($chunk->getBiomeId($x & 0x0f, $z & 0x0f), $sourceX + $x, $y, $sourceZ + $z)){
					$block = $world->getBlockAt($x, $y, $z);
					$blockAbove = $world->getBlockAt($x, $y + 1, $z);
					switch($block->getId()){
						case BlockLegacyIds::WATER:
						case BlockLegacyIds::STILL_WATER:
						case BlockLegacyIds::SNOW:
						case BlockLegacyIds::ICE:
						case BlockLegacyIds::PACKED_ICE:
						case BlockLegacyIds::YELLOW_FLOWER:
						case BlockLegacyIds::RED_FLOWER:
						case BlockLegacyIds::TALL_GRASS:
						case BlockLegacyIds::DOUBLE_PLANT:
						case BlockLegacyIds::SUGARCANE_BLOCK:
						case BlockLegacyIds::LAVA:
						case BlockLegacyIds::STILL_LAVA:
							break;
						case BlockLegacyIds::DIRT:
							$world->setBlockAt($x, $y, $z, VanillaBlocks::GRASS());
							if($blockAbove->getId() === BlockLegacyIds::AIR){
								$world->setBlockAt($x, $y + 1, $z, VanillaBlocks::SNOW_LAYER());
							}
							break;
						default:
							if($blockAbove->getId() === BlockLegacyIds::AIR){
								$world->setBlockAt($x, $y + 1, $z, VanillaBlocks::SNOW_LAYER());
							}
							break;
					}
				}
			}
		}
	}
}