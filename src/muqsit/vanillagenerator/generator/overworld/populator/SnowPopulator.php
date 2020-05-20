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

	public function populate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = $chunk->getX() << 4;
		$sourceZ = $chunk->getZ() << 4;

		$block_factory = BlockFactory::getInstance();
		$air = VanillaBlocks::AIR()->getFullId();
		$grass = VanillaBlocks::GRASS()->getFullId();
		$snow_layer = VanillaBlocks::SNOW_LAYER()->getFullId();

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$y = $chunk->getHighestBlockAt($x, $z) - 1;
				if(BiomeClimateManager::isSnowy($chunk->getBiomeId($x, $z), $sourceX + $x, $y, $sourceZ + $z)){
					switch($block_factory->fromFullBlock($chunk->getFullBlock($x, $y, $z))->getId()){
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