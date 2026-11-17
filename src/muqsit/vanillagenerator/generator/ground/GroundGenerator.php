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
use function hrtime;
use function number_format;

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

		$top_mat_initial = $top_mat = $this->top_material->getStateId();
		$ground_mat_initial = $ground_mat = $this->ground_material->getStateId();
		$ground_mat_id_initial = $ground_mat_id = $this->ground_material->getTypeId();

		$chunk_x = $x;
		$chunk_z = $z;

		$surface_height = max((int) ($surface_noise / 3.0 + 3.0 + $random->nextFloat() * 0.25), 1);
		$deep = -1;

		static $air = VanillaBlocks::AIR()->getStateId();
		static $stone = VanillaBlocks::STONE()->getStateId();
		static $sandstone = VanillaBlocks::SANDSTONE()->getStateId();
		static $gravel = VanillaBlocks::GRAVEL()->getStateId();
		static $bedrock = VanillaBlocks::BEDROCK()->getStateId();
		static $ice = VanillaBlocks::ICE()->getStateId();

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		$block_x = $x & Chunk::COORD_MASK;
		$block_z = $z & Chunk::COORD_MASK;

		static $water = null;
		if($water === null){
			$water = [];
			foreach(RuntimeBlockStateRegistry::getInstance()->getAllKnownStates() as $block){
				if($block instanceof Water && $block->isStill()){
					$water[$block->getStateId()] = true;
				}
			}
		}

		for($y = 255; $y >= 0; --$y){
			if($y <= $random->nextBoundedInt($this->bedrock_roughness)){
				$chunk->setBlockStateId($block_x, $y, $block_z, $bedrock);
			}else{
				$mat_state = $chunk->getBlockStateId($block_x, $y, $block_z);
				if($mat_state === $air){
					$deep = -1;
				}elseif($mat_state === $stone){
					if($deep === -1){
						if($y >= $sea_level - 5 && $y <= $sea_level){
							$top_mat = $top_mat_initial;
							$ground_mat = $ground_mat_initial;
							$ground_mat_id = $ground_mat_id_initial;
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
				}elseif(isset($water[$mat_state]) && $y === $sea_level - 2 && BiomeClimateManager::isCold($biome, $chunk_x, $y, $chunk_z)){
					$chunk->setBlockStateId($block_x, $y, $block_z, $ice);
				}
			}
		}
	}
}