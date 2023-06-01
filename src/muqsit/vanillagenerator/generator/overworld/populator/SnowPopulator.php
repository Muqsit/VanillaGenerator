<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class SnowPopulator implements Populator{

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << Chunk::COORD_BIT_SIZE;
		$source_z = $chunk_z << Chunk::COORD_BIT_SIZE;

		$block_state_registry = RuntimeBlockStateRegistry::getInstance();
		$air = VanillaBlocks::AIR()->getStateId();
		$grass = VanillaBlocks::GRASS()->getStateId();
		$snow_layer = VanillaBlocks::SNOW_LAYER()->getStateId();

		$world_height = $world->getMaxY();

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$highest_y = $chunk->getHighestBlockAt($x, $z);
				if($highest_y > 0 && $highest_y < $world_height - 1){
					$y = $highest_y - 1;
					if(BiomeClimateManager::isSnowy($chunk->getBiomeId($x, 0, $z), $source_x + $x, $y, $source_z + $z)){
						switch($block_state_registry->fromStateId($chunk->getBlockStateId($x, $y, $z))->getTypeId()){
							case BlockTypeIds::WATER:
							case BlockTypeIds::SNOW:
							case BlockTypeIds::ICE:
							case BlockTypeIds::PACKED_ICE:
							case BlockTypeIds::DANDELION:
							case BlockTypeIds::POPPY:
							case BlockTypeIds::TALL_GRASS:
							case BlockTypeIds::DOUBLE_TALLGRASS:
							case BlockTypeIds::SUGARCANE:
							case BlockTypeIds::LAVA:
								break;
							case BlockTypeIds::DIRT:
								$chunk->setBlockStateId($x, $y, $z, $grass);
								if($chunk->getBlockStateId($x, $y + 1, $z) === $air){
									$chunk->setBlockStateId($x, $y + 1, $z, $snow_layer);
								}
								break;
							default:
								if($chunk->getBlockStateId($x, $y + 1, $z) === $air){
									$chunk->setBlockStateId($x, $y + 1, $z, $snow_layer);
								}
								break;
						}
					}
				}
			}
		}
	}
}