<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class SnowPopulator implements Populator{

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << 4;
		$source_z = $chunk_z << 4;

		$block_factory = BlockFactory::getInstance();
		$air = VanillaBlocks::AIR()->getFullId();
		$grass = VanillaBlocks::GRASS()->getFullId();
		$snow_layer = VanillaBlocks::SNOW_LAYER()->getFullId();

		$world_height = $world->getMaxY();

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$highest_y = $chunk->getHighestBlockAt($x, $z);
				if($highest_y > 0 && $highest_y < $world_height - 1){
					$y = $highest_y - 1;
					if(BiomeClimateManager::isSnowy($chunk->getBiomeId($x, $z), $source_x + $x, $y, $source_z + $z)){
						switch($block_factory->fromFullBlock($chunk->getFullBlock($x, $y, $z))->getId()){
							case BlockLegacyIds::FLOWING_WATER:
							case BlockLegacyIds::STILL_WATER:
							case BlockLegacyIds::SNOW:
							case BlockLegacyIds::ICE:
							case BlockLegacyIds::PACKED_ICE:
							case BlockLegacyIds::YELLOW_FLOWER:
							case BlockLegacyIds::RED_FLOWER:
							case BlockLegacyIds::TALL_GRASS:
							case BlockLegacyIds::DOUBLE_PLANT:
							case BlockLegacyIds::SUGARCANE_BLOCK:
							case BlockLegacyIds::FLOWING_LAVA:
							case BlockLegacyIds::STILL_LAVA:
								break;
							case BlockLegacyIds::DIRT:
								$chunk->setFullBlock($x, $y, $z, $grass);
								if($chunk->getFullBlock($x, $y + 1, $z) === $air){
									$chunk->setFullBlock($x, $y + 1, $z, $snow_layer);
								}
								break;
							default:
								if($chunk->getFullBlock($x, $y + 1, $z) === $air){
									$chunk->setFullBlock($x, $y + 1, $z, $snow_layer);
								}
								break;
						}
					}
				}
			}
		}
	}
}