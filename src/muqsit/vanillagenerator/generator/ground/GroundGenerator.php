<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class GroundGenerator{

	/** @var Block */
	private $topMaterial;

	/** @var Block */
	private $groundMaterial;

	public function __construct(?Block $topMaterial = null, ?Block $groundMaterial = null){
		$this->setTopMaterial($topMaterial ?? VanillaBlocks::GRASS());
		$this->setGroundMaterial($groundMaterial ?? VanillaBlocks::DIRT());
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

		$topMat = $this->topMaterial->getFullId();
		$groundMat = $this->groundMaterial->getFullId();
		$groundMatId = $this->groundMaterial->getId();

		$chunkX = $x;
		$chunkZ = $z;

		$surfaceHeight = max((int) ($surfaceNoise / 3.0 + 3.0 + $random->nextFloat() * 0.25), 1);
		$deep = -1;

		$block_factory = BlockFactory::getInstance();
		$air = VanillaBlocks::AIR()->getFullId();
		$stone = VanillaBlocks::STONE()->getFullId();
		$sandstone = VanillaBlocks::SANDSTONE()->getFullId();
		$gravel = VanillaBlocks::GRAVEL()->getFullId();
		$bedrock = VanillaBlocks::BEDROCK()->getFullId();
		$ice = VanillaBlocks::ICE()->getFullId();

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($x >> 4, $z >> 4);
		$block_x = $x & 0x0f;
		$block_z = $z & 0x0f;

		for($y = 255; $y >= 0; --$y){
			if($y <= $random->nextBoundedInt(5)){
				$chunk->setFullBlock($block_x, $y, $block_z, $bedrock);
			}else{
				$matId = $block_factory->fromFullBlock($chunk->getFullBlock($block_x, $y, $block_z))->getId();
				if($matId === BlockLegacyIds::AIR){
					$deep = -1;
				}elseif($matId === BlockLegacyIds::STONE){
					if($deep === -1){
						if($y >= $seaLevel - 5 && $y <= $seaLevel){
							$topMat = $this->topMaterial->getFullId();
							$groundMat = $this->groundMaterial->getFullId();
							$groundMatId = $this->groundMaterial->getId();
						}

						$deep = $surfaceHeight;
						if($y >= $seaLevel - 2){
							$chunk->setFullBlock($block_x, $y, $block_z, $topMat);
						}elseif($y < $seaLevel - 8 - $surfaceHeight){
							$topMat = $air;
							$groundMat = $stone;
							$groundMatId = BlockLegacyIds::STONE;
							$chunk->setFullBlock($block_x, $y, $block_z, $gravel);
						}else{
							$chunk->setFullBlock($block_x, $y, $block_z, $groundMat);
						}
					}elseif($deep > 0){
						--$deep;
						$chunk->setFullBlock($block_x, $y, $block_z, $groundMat);

						if($deep === 0 && $groundMatId === BlockLegacyIds::SAND){
							$deep = $random->nextBoundedInt(4) + max(0, $y - $seaLevel - 1);
							$groundMat = $sandstone;
							$groundMatId = BlockLegacyIds::SANDSTONE;
						}
					}
				}elseif($matId === BlockLegacyIds::STILL_WATER && $y === $seaLevel - 2 && BiomeClimateManager::isCold($biome, $chunkX, $y, $chunkZ)){
					$chunk->setFullBlock($block_x, $y, $block_z, $ice);
				}
			}
		}
	}
}