<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class GroundGenerator{

	/** @var Block */
	protected static $AIR;

	/** @var Block */
	protected static $STONE;

	/** @var Block */
	protected static $SANDSTONE;

	/** @var Block */
	protected static $GRASS;

	/** @var Block */
	protected static $DIRT;

	/** @var Block */
	protected static $COARSE_DIRT;

	/** @var Block */
	protected static $PODZOL;

	/** @var Block */
	protected static $GRAVEL;

	/** @var Block */
	protected static $MYCEL;

	/** @var Block */
	protected static $SAND;

	/** @var Block */
	protected static $SNOW;

	public static function init() : void{
		self::$AIR = BlockFactory::get(BlockLegacyIds::AIR);
		self::$STONE = BlockFactory::get(BlockLegacyIds::STONE);
		self::$SANDSTONE = BlockFactory::get(BlockLegacyIds::SANDSTONE);
		self::$GRASS = BlockFactory::get(BlockLegacyIds::GRASS);
		self::$DIRT = BlockFactory::get(BlockLegacyIds::DIRT);
		self::$COARSE_DIRT = BlockFactory::get(BlockLegacyIds::DIRT, BlockLegacyMetadata::DIRT_COARSE);
		self::$PODZOL = BlockFactory::get(BlockLegacyIds::PODZOL);
		self::$GRAVEL = BlockFactory::get(BlockLegacyIds::GRAVEL);
		self::$MYCEL = BlockFactory::get(BlockLegacyIds::MYCELIUM);
		self::$SAND = BlockFactory::get(BlockLegacyIds::SAND);
		self::$SNOW = BlockFactory::get(BlockLegacyIds::SNOW_BLOCK);
	}

	/** @var Block */
	private $topMaterial;

	/** @var Block */
	private $groundMaterial;

	public function __construct(){
		$this->setTopMaterial(self::$GRASS);
		$this->setGroundMaterial(self::$DIRT);
	}

	/**
	 * Generates a terrain column.
	 *
	 * @param ChunkManager $world the affected world
	 * @param Random $random the PRNG to use
	 * @param int $x the chunk X coordinate
	 * @param int $z the chunk Z coordinate
	 * @param int $biome the biome this column is in
	 * @param float $surfaceNoise the amplitude of random variation in surface height
	 */
	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surfaceNoise) : void{
		$seaLevel = 64;

		$topMat = $this->topMaterial;
		$groundMat = $this->groundMaterial;

		$chunkX = $x;
		$chunkZ = $z;

		$surfaceHeight = max((int) ($surfaceNoise / 3.0 + 3.0 + $random->nextFloat() * 0.25), 1);
		$deep = -1;
		for($y = 255; $y >= 0; --$y){
			if($y <= $random->nextBoundedInt(5)){
				$world->setBlockAt($x, $y, $z, BlockFactory::get(BlockLegacyIds::BEDROCK));
			}else{
				$mat = $world->getBlockAt($x, $y, $z);
				$matId = $mat->getId();
				if($matId === BlockLegacyIds::AIR){
					$deep = -1;
				}elseif($matId === BlockLegacyIds::STONE){
					if($deep === -1){
						if($y >= $seaLevel - 5 && $y <= $seaLevel){
							$topMat = $this->topMaterial;
							$groundMat = $this->groundMaterial;
						}

						$deep = $surfaceHeight;
						if($y >= $seaLevel - 2){
							$world->setBlockAt($x, $y, $z, $topMat);
						}elseif($y < $seaLevel - 8 - $surfaceHeight){
							$topMat = self::$AIR;
							$groundMat = self::$STONE;
							$world->setBlockAt($x, $y, $z, BlockFactory::get(BlockLegacyIds::GRAVEL));
						}else{
							$world->setBlockAt($x, $y, $z, $groundMat);
						}
					}elseif($deep > 0){
						--$deep;
						$world->setBlockAt($x, $y, $z, $groundMat);

						if($deep === 0 && $groundMat->getId() === BlockLegacyIds::SAND){
							$deep = $random->nextBoundedInt(4) + max(0, $y - $seaLevel - 1);
							$groundMat = self::$SANDSTONE;
						}
					}
				}elseif($matId === BlockLegacyIds::STILL_WATER && $y === $seaLevel - 2 && BiomeClimateManager::isCold($biome, $chunkX, $y, $chunkZ)){
					$world->setBlockAt($x, $y, $z, BlockFactory::get(BlockLegacyIds::ICE));
				}
			}
		}
	}

	final protected function setTopMaterial(Block $topMaterial) : void{
		$this->topMaterial = $topMaterial;
	}

	final protected function setGroundMaterial(Block $groundMaterial) : void{
		$this->groundMaterial = $groundMaterial;
	}
}

GroundGenerator::init();