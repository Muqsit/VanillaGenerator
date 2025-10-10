<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Liquid;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\block\Wood;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function array_key_exists;

class Lake extends TerrainObject{

	private const MAX_DIAMETER = 16.0;
	private const MAX_HEIGHT = 8.0;

	/** @var int[] */
	private static array $MYCEL_BIOMES;

	public static function init() : void{
		self::$MYCEL_BIOMES = [];
		foreach([BiomeIds::MUSHROOM_ISLAND, BiomeIds::MUSHROOM_ISLAND_SHORE] as $block_id){
			self::$MYCEL_BIOMES[$block_id] = $block_id;
		}
	}

	public function __construct(
		private Block $type
	){}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$succeeded = false;
		$source_y -= (int) self::MAX_HEIGHT / 2;

		$lake_map = [];
		for($n = 0; $n < $random->nextBoundedInt(4) + 4; ++$n){
			$size_x = $random->nextFloat() * 6.0 + 3;
			$size_y = $random->nextFloat() * 4.0 + 2;
			$size_z = $random->nextFloat() * 6.0 + 3;
			$dx = $random->nextFloat() * (self::MAX_DIAMETER - $size_x - 2) + 1 + $size_x / 2.0;
			$dy = $random->nextFloat() * (self::MAX_HEIGHT - $size_y - 4) + 2 + $size_y / 2.0;
			$dz = $random->nextFloat() * (self::MAX_DIAMETER - $size_z - 2) + 1 + $size_z / 2.0;
			for($x = 1; $x < (int) self::MAX_DIAMETER - 1; ++$x){
				for($z = 1; $z < (int) self::MAX_DIAMETER - 1; ++$z){
					for($y = 1; $y < (int) self::MAX_HEIGHT - 1; ++$y){
						$nx = ($x - $dx) / ($size_x / 2.0);
						$nx *= $nx;
						$ny = ($y - $dy) / ($size_y / 2.0);
						$ny *= $ny;
						$nz = ($z - $dz) / ($size_z / 2.0);
						$nz *= $nz;
						if($nx + $ny + $nz < 1.0){
							$this->setLakeBlock($lake_map, $x, $y, $z);
							$succeeded = true;
						}
					}
				}
			}
		}

		if(!$this->canPlace($lake_map, $world, $source_x, $source_y, $source_z)){
			return $succeeded;
		}

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($source_x >> Chunk::COORD_BIT_SIZE, $source_z >> Chunk::COORD_BIT_SIZE);
		$biome = $chunk->getBiomeId(($source_x + 8 + (int) self::MAX_DIAMETER / 2) & Chunk::COORD_MASK, $source_y, ($source_z + 8 + (int) self::MAX_DIAMETER / 2) & Chunk::COORD_MASK);
		$mycel_biome = array_key_exists($biome, self::$MYCEL_BIOMES);

		$max_diameter = (int) self::MAX_DIAMETER;
		for($x = 0; $x < $max_diameter; ++$x){
			for($z = 0; $z < $max_diameter; ++$z){
				for($y = 0; $y < $max_diameter; ++$y){
					if(!$this->isLakeBlock($lake_map, $x, $y, $z)){
						continue;
					}

					$type = $this->type;
					$block = $world->getBlockAt($source_x + $x, $source_y + $y, $source_z + $z);
					$block_above = $world->getBlockAt($source_x + $x, $source_y + $y + 1, $source_z + $z);
					$block_type = $block->getTypeId();
					if(($block_type === BlockTypeIds::DIRT && $block_above instanceof Wood) || $block instanceof Wood){
						continue;
					}

					if($y >= (int) (self::MAX_HEIGHT / 2)){
						$type = VanillaBlocks::AIR();
						if(TerrainObject::killWeakBlocksAbove($world, $source_x + $x, $source_y + $y, $source_z + $z)){
							break;
						}

						if(($block_type === BlockTypeIds::ICE || $block_type === BlockTypeIds::PACKED_ICE) && $this->type instanceof Water && $this->type->isStill()){
							$type = $block;
						}
					}elseif($y === (int) (self::MAX_HEIGHT / 2 - 1)){
						if($type instanceof Water && $type->isStill() && BiomeClimateManager::isCold($chunk->getBiomeId($x & Chunk::COORD_MASK, $y, $z & Chunk::COORD_MASK), $source_x + $x, $y, $source_z + $z)){
							$type = VanillaBlocks::ICE();
						}
					}
					$world->setBlockAt($source_x + $x, $source_y + $y, $source_z + $z, $type);
				}
			}
		}

		for($x = 0; $x < (int) self::MAX_DIAMETER; ++$x){
			for($z = 0; $z < (int) self::MAX_DIAMETER; ++$z){
				for($y = (int) self::MAX_HEIGHT / 2; $y < (int) self::MAX_HEIGHT; ++$y){
					if(!$this->isLakeBlock($lake_map, $x, $y, $z)){
						continue;
					}

					$block = $world->getBlockAt($source_x + $x, $source_y + $y - 1, $source_z + $z);
					$block_above = $world->getBlockAt($source_x + $x, $source_y + $y, $source_z + $z);
					if($block->getTypeId() === BlockTypeIds::DIRT && $block_above->isTransparent() && $block_above->getLightLevel() > 0){
						$world->setBlockAt($source_x + $x, $source_y + $y - 1, $source_z + $z, $mycel_biome ? VanillaBlocks::MYCELIUM() : VanillaBlocks::GRASS());
					}
				}
			}
		}
		return $succeeded;
	}

	/**
	 * @param int[] $lake_map
	 * @param ChunkManager $world
	 * @param int $sourceX
	 * @param int $sourceY
	 * @param int $sourceZ
	 * @return bool
	 */
	private function canPlace(array $lake_map, ChunkManager $world, int $sourceX, int $sourceY, int $sourceZ) : bool{
		for($x = 0; $x < self::MAX_DIAMETER; ++$x){
			for($z = 0; $z < self::MAX_DIAMETER; ++$z){
				for($y = 0; $y < self::MAX_HEIGHT; ++$y){
					if($this->isLakeBlock($lake_map, $x, $y, $z)
						|| ((($x >= (self::MAX_DIAMETER - 1)) || !$this->isLakeBlock($lake_map, $x + 1, $y, $z))
							&& (($x <= 0) || !$this->isLakeBlock($lake_map, $x - 1, $y, $z))
							&& (($z >= (self::MAX_DIAMETER - 1)) || !$this->isLakeBlock($lake_map, $x, $y, $z + 1))
							&& (($z <= 0) || !$this->isLakeBlock($lake_map, $x, $y, $z - 1))
							&& (($z >= (self::MAX_HEIGHT - 1)) || !$this->isLakeBlock($lake_map, $x, $y + 1, $z))
							&& (($z <= 0) || !$this->isLakeBlock($lake_map, $x, $y - 1, $z)))){
						continue;
					}
					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					if($y >= self::MAX_HEIGHT / 2 && (($block instanceof Liquid) || $block->getTypeId() === BlockTypeIds::ICE)){
						return false; // there's already some liquids above
					}
					if($y < self::MAX_HEIGHT / 2 && !$block->isSolid() && $block->getTypeId() !== $this->type->getTypeId()){
						return false;
						// bottom must be solid and do not overlap with another liquid type
					}
				}
			}
		}
		return true;
	}

	/**
	 * @param int[] $lake_map
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return bool
	 */
	private function isLakeBlock(array $lake_map, int $x, int $y, int $z) : bool{
		return ($lake_map[($x * (int) self::MAX_DIAMETER + $z) * (int) self::MAX_HEIGHT + $y] ?? 0) !== 0;
	}

	/**
	 * @param int[] $lake_map
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 */
	private function setLakeBlock(array &$lake_map, int $x, int $y, int $z) : void{
		$lake_map[($x * (int) self::MAX_DIAMETER + $z) * (int) self::MAX_HEIGHT + $y] = 1;
	}
}
Lake::init();