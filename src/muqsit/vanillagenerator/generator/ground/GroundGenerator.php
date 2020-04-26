<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class GroundGenerator{

	/** @var Block */
	private $topMaterial;

	/** @var Block */
	private $groundMaterial;

	public function __construct(){
		$this->setTopMaterial(VanillaBlocks::GRASS());
		$this->setGroundMaterial(VanillaBlocks::DIRT());
	}

	final protected function setTopMaterial(Block $topMaterial) : void{
		$this->topMaterial = $topMaterial;
	}

	final protected function setGroundMaterial(Block $groundMaterial) : void{
		$this->groundMaterial = $groundMaterial;
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

		$air = VanillaBlocks::AIR();
		$stone = VanillaBlocks::STONE();
		$sandstone = VanillaBlocks::SANDSTONE();
		$gravel = VanillaBlocks::GRAVEL();

		for($y = 255; $y >= 0; --$y){
			if($y <= $random->nextBoundedInt(5)){
				$world->setBlockAt($x, $y, $z, VanillaBlocks::BEDROCK());
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
							$topMat = $air;
							$groundMat = $stone;
							$world->setBlockAt($x, $y, $z, $gravel);
						}else{
							$world->setBlockAt($x, $y, $z, $groundMat);
						}
					}elseif($deep > 0){
						--$deep;
						$world->setBlockAt($x, $y, $z, $groundMat);

						if($deep === 0 && $groundMat->getId() === BlockLegacyIds::SAND){
							$deep = $random->nextBoundedInt(4) + max(0, $y - $seaLevel - 1);
							$groundMat = $sandstone;
						}
					}
				}elseif($matId === BlockLegacyIds::STILL_WATER && $y === $seaLevel - 2 && BiomeClimateManager::isCold($biome, $chunkX, $y, $chunkZ)){
					$world->setBlockAt($x, $y, $z, VanillaBlocks::ICE());
				}
			}
		}
	}
}