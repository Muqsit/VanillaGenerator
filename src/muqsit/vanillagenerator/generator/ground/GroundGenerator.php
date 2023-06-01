<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class GroundGenerator{

	protected Block $top_material;
	protected Block $ground_material;
	protected int $bedrock_roughness = 5;

	public function __construct(?Block $top_material = null, ?Block $ground_material = null){
		$this->setTopMaterial($top_material ?? VanillaBlocks::GRASS());
		$this->setGroundMaterial($ground_material ?? VanillaBlocks::DIRT());
	}

	public function getBedrockRoughness() : int{
		return $this->bedrock_roughness;
	}

	public function setBedrockRoughness(int $bedrock_roughness) : void{
		$this->bedrock_roughness = $bedrock_roughness;
	}

	final protected function setTopMaterial(Block $top_material) : void{
		$this->top_material = $top_material;
	}

	final protected function setGroundMaterial(Block $ground_material) : void{
		$this->ground_material = $ground_material;
	}

	/**
	 * Generates a terrain column.
	 *
	 * @param ChunkManager $world the affected world
	 * @param Random $random the PRNG to use
	 * @param int $x the chunk X coordinate
	 * @param int $z the chunk Z coordinate
	 * @param int $biome the biome this column is in
	 * @param float $surface_noise the amplitude of random variation in surface height
	 */
	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surface_noise) : void{
		$sea_level = 64;

		$top_mat = $this->top_material->getStateId();
		$ground_mat = $this->ground_material->getStateId();
		$ground_mat_id = $this->ground_material->getTypeId();

		$chunk_x = $x;
		$chunk_z = $z;

		$surface_height = max((int) ($surface_noise / 3.0 + 3.0 + $random->nextFloat() * 0.25), 1);
		$deep = -1;

		$block_state_registry = RuntimeBlockStateRegistry::getInstance();
		$air = VanillaBlocks::AIR()->getStateId();
		$stone = VanillaBlocks::STONE()->getStateId();
		$sandstone = VanillaBlocks::SANDSTONE()->getStateId();
		$gravel = VanillaBlocks::GRAVEL()->getStateId();
		$bedrock = VanillaBlocks::BEDROCK()->getStateId();
		$ice = VanillaBlocks::ICE()->getStateId();

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		$block_x = $x & Chunk::COORD_MASK;
		$block_z = $z & Chunk::COORD_MASK;

		for($y = 255; $y >= 0; --$y){
			if($y <= $random->nextBoundedInt($this->bedrock_roughness)){
				$chunk->setBlockStateId($block_x, $y, $block_z, $bedrock);
			}else{
				$mat = $block_state_registry->fromStateId($chunk->getBlockStateId($block_x, $y, $block_z));
				$mat_id = $mat->getTypeId();
				if($mat_id === BlockTypeIds::AIR){
					$deep = -1;
				}elseif($mat_id === BlockTypeIds::STONE){
					if($deep === -1){
						if($y >= $sea_level - 5 && $y <= $sea_level){
							$top_mat = $this->top_material->getStateId();
							$ground_mat = $this->ground_material->getStateId();
							$ground_mat_id = $this->ground_material->getTypeId();
						}

						$deep = $surface_height;
						if($y >= $sea_level - 2){
							$chunk->setBlockStateId($block_x, $y, $block_z, $top_mat);
						}elseif($y < $sea_level - 8 - $surface_height){
							$top_mat = $air;
							$ground_mat = $stone;
							$ground_mat_id = BlockTypeIds::STONE;
							$chunk->setBlockStateId($block_x, $y, $block_z, $gravel);
						}else{
							$chunk->setBlockStateId($block_x, $y, $block_z, $ground_mat);
						}
					}elseif($deep > 0){
						--$deep;
						$chunk->setBlockStateId($block_x, $y, $block_z, $ground_mat);

						if($deep === 0 && $ground_mat_id === BlockTypeIds::SAND){
							$deep = $random->nextBoundedInt(4) + max(0, $y - $sea_level - 1);
							$ground_mat = $sandstone;
							$ground_mat_id = BlockTypeIds::SANDSTONE;
						}
					}
				}elseif($mat instanceof Water && $mat->isStill() && $y === $sea_level - 2 && BiomeClimateManager::isCold($biome, $chunk_x, $y, $chunk_z)){
					$chunk->setBlockStateId($block_x, $y, $block_z, $ice);
				}
			}
		}
	}
}