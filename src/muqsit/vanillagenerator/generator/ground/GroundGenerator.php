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
	protected Block $top_material;

	/** @var Block */
	protected Block $ground_material;

	/** @var int */
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

		$top_mat = $this->top_material->getFullId();
		$ground_mat = $this->ground_material->getFullId();
		$ground_mat_id = $this->ground_material->getId();

		$chunk_x = $x;
		$chunk_z = $z;

		$surface_height = max((int) ($surface_noise / 3.0 + 3.0 + $random->nextFloat() * 0.25), 1);
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
			if($y <= $random->nextBoundedInt($this->bedrock_roughness)){
				$chunk->setFullBlock($block_x, $y, $block_z, $bedrock);
			}else{
				$mat_id = $block_factory->fromFullBlock($chunk->getFullBlock($block_x, $y, $block_z))->getId();
				if($mat_id === BlockLegacyIds::AIR){
					$deep = -1;
				}elseif($mat_id === BlockLegacyIds::STONE){
					if($deep === -1){
						if($y >= $sea_level - 5 && $y <= $sea_level){
							$top_mat = $this->top_material->getFullId();
							$ground_mat = $this->ground_material->getFullId();
							$ground_mat_id = $this->ground_material->getId();
						}

						$deep = $surface_height;
						if($y >= $sea_level - 2){
							$chunk->setFullBlock($block_x, $y, $block_z, $top_mat);
						}elseif($y < $sea_level - 8 - $surface_height){
							$top_mat = $air;
							$ground_mat = $stone;
							$ground_mat_id = BlockLegacyIds::STONE;
							$chunk->setFullBlock($block_x, $y, $block_z, $gravel);
						}else{
							$chunk->setFullBlock($block_x, $y, $block_z, $ground_mat);
						}
					}elseif($deep > 0){
						--$deep;
						$chunk->setFullBlock($block_x, $y, $block_z, $ground_mat);

						if($deep === 0 && $ground_mat_id === BlockLegacyIds::SAND){
							$deep = $random->nextBoundedInt(4) + max(0, $y - $sea_level - 1);
							$ground_mat = $sandstone;
							$ground_mat_id = BlockLegacyIds::SANDSTONE;
						}
					}
				}elseif($mat_id === BlockLegacyIds::STILL_WATER && $y === $sea_level - 2 && BiomeClimateManager::isCold($biome, $chunk_x, $y, $chunk_z)){
					$chunk->setFullBlock($block_x, $y, $block_z, $ice);
				}
			}
		}
	}
}