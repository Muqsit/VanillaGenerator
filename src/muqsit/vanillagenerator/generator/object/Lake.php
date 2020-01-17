<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Liquid;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class Lake extends TerrainObject{

	private const MAX_DIAMETER = 16.0;
	private const MAX_HEIGHT = 8.0;
	private const MYCEL_BIOMES = [BiomeIds::MUSHROOM_ISLAND, BiomeIds::MUSHROOM_ISLAND_SHORE];

	/** @var Block */
	private $type;

	public function __construct(Block $type){
		$this->type = $type;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$succeeded = false;
		$sourceY -= (int) self::MAX_HEIGHT / 2;

		$lakeMap = [];
		for($n = 0; $n < $random->nextBoundedInt(4) + 4; ++$n){
			$sizeX = $random->nextFloat() * 6.0 + 3;
			$sizeY = $random->nextFloat() * 4.0 + 2;
			$sizeZ = $random->nextFloat() * 6.0 + 3;
			$dx = $random->nextFloat() * (self::MAX_DIAMETER - $sizeX - 2) + 1 + $sizeX / 2.0;
			$dy = $random->nextFloat() * (self::MAX_HEIGHT - $sizeY - 4) + 2 + $sizeY / 2.0;
			$dz = $random->nextFloat() * (self::MAX_DIAMETER - $sizeZ - 2) + 1 + $sizeZ / 2.0;
			for($x = 1; $x < (int) self::MAX_DIAMETER - 1; ++$x){
				for($z = 1; $z < (int) self::MAX_DIAMETER - 1; ++$z){
					for($y = 1; $y < (int) self::MAX_HEIGHT - 1; ++$y){
						$nx = ($x - $dx) / ($sizeX / 2.0);
						$nx *= $nx;
						$ny = ($y - $dy) / ($sizeY / 2.0);
						$ny *= $ny;
						$nz = ($z - $dz) / ($sizeZ / 2.0);
						$nz *= $nz;
						if($nx + $ny + $nz < 1.0){
							$this->setLakeBlock($lakeMap, $x, $y, $z);
							$succeeded = true;
						}
					}
				}
			}
		}

		if(!$this->canPlace($lakeMap, $world, $sourceX, $sourceY, $sourceZ)){
			return $succeeded;
		}

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($sourceX >> 4, $sourceZ >> 4);
		$biome = $chunk->getBiomeId(($sourceX + 8 + (int) self::MAX_DIAMETER / 2) & 0x0f, ($sourceZ + 8 + (int) self::MAX_DIAMETER / 2) & 0x0f);
		$mycelBiome = in_array($biome, self::MYCEL_BIOMES, true);

		for($x = 0; $x < (int) self::MAX_DIAMETER; ++$x){
			for($z = 0; $z < (int) self::MAX_DIAMETER; ++$z){
				for($y = 0; $y < (int) self::MAX_HEIGHT; ++$y){
					if(!$this->isLakeBlock($lakeMap, $x, $y, $z)){
						continue;
					}

					$type = $this->type;
					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					$blockAbove = $world->getBlockAt($sourceX + $x, $sourceY + $y + 1, $sourceZ + $z);
					$blockType = $block->getId();
					$blockAboveType = $blockAbove->getId();
					if(($blockType === BlockLegacyIds::DIRT && ($blockAboveType === BlockLegacyIds::LOG || $blockAboveType === BlockLegacyIds::LOG2)) || $blockType === BlockLegacyIds::LOG || $blockType === BlockLegacyIds::LOG2){
						continue;
					}

					if($y >= (int) self::MAX_HEIGHT / 2){
						$type = VanillaBlocks::AIR();
						if(TerrainObject::killPlantAbove($world, $sourceX + $x, $sourceY + $y, $sourceZ + $z)){
							break;
						}

						if($this->type->getId() === BlockLegacyIds::STILL_WATER && ($blockType === BlockLegacyIds::ICE || $blockType === BlockLegacyIds::PACKED_ICE)){
							$type = $blockType;
						}
					}elseif($y === self::MAX_HEIGHT / 2 - 1){
						if($type->getId() === BlockLegacyIds::STILL_WATER && BiomeClimateManager::isCold($chunk->getBiomeId($x & 0x0f, $z & 0x0f), $sourceX + $x, $y, $sourceZ + $z)){
							$type = VanillaBlocks::ICE();
						}
					}
					$world->setBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z, $type);
				}
			}
		}

		for($x = 0; $x < (int) self::MAX_DIAMETER; ++$x){
			for($z = 0; $z < (int) self::MAX_DIAMETER; ++$z){
				for($y = (int) self::MAX_HEIGHT / 2; $y < (int) self::MAX_HEIGHT; ++$y){
					if(!$this->isLakeBlock($lakeMap, $x, $y, $z)){
						continue;
					}

					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y - 1, $sourceZ + $z);
					$blockAbove = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					if($block->getId() === BlockLegacyIds::DIRT && $blockAbove->isTransparent() && $blockAbove->getLightLevel() > 0){
						$world->setBlockAt($sourceX + $x, $sourceY + $y - 1, $sourceZ + $z, $mycelBiome ? VanillaBlocks::MYCELIUM() : VanillaBlocks::GRASS());
					}
				}
			}
		}
		return $succeeded;
	}

	private function canPlace(array $lakeMap, ChunkManager $world, int $sourceX, int $sourceY, int $sourceZ) : bool{
		for($x = 0; $x < self::MAX_DIAMETER; ++$x){
			for($z = 0; $z < self::MAX_DIAMETER; ++$z){
				for($y = 0; $y < self::MAX_HEIGHT; ++$y){
					if($this->isLakeBlock($lakeMap, $x, $y, $z)
						|| ((($x >= (self::MAX_DIAMETER - 1)) || !$this->isLakeBlock($lakeMap, $x + 1, $y, $z))
							&& (($x <= 0) || !$this->isLakeBlock($lakeMap, $x - 1, $y, $z))
							&& (($z >= (self::MAX_DIAMETER - 1)) || !$this->isLakeBlock($lakeMap, $x, $y, $z + 1))
							&& (($z <= 0) || !$this->isLakeBlock($lakeMap, $x, $y, $z - 1))
							&& (($z >= (self::MAX_HEIGHT - 1)) || !$this->isLakeBlock($lakeMap, $x, $y + 1, $z))
							&& (($z <= 0) || !$this->isLakeBlock($lakeMap, $x, $y - 1, $z)))){
						continue;
					}
					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					if($y >= self::MAX_HEIGHT / 2 && (($block instanceof Liquid) || $block->getId() === BlockLegacyIds::ICE)){
						return false; // there's already some liquids above
					}
					if($y < self::MAX_HEIGHT / 2 && !$block->isSolid() && $block->getId() !== $this->type->getId()){
						return false;
						// bottom must be solid and do not overlap with another liquid type
					}
				}
			}
		}
		return true;
	}

	private function isLakeBlock(array $lakeMap, int $x, int $y, int $z) : bool{
		return ($lakeMap[($x * (int) self::MAX_DIAMETER + $z) * (int) self::MAX_HEIGHT + $y] ?? 0) !== 0;
	}

	private function setLakeBlock(array &$lakeMap, int $x, int $y, int $z) : void{
		$lakeMap[($x * (int) self::MAX_DIAMETER + $z) * (int) self::MAX_HEIGHT + $y] = 1;
	}
}