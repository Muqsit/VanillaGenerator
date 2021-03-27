<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function array_key_exists;

class SwampTree extends CocoaTree{

	/** @var int[] */
	private static array $WATER_BLOCK_TYPES;

	public static function init() : void{
		self::$WATER_BLOCK_TYPES = [];
		foreach([BlockLegacyIds::FLOWING_WATER, BlockLegacyIds::STILL_WATER] as $block_id){
			self::$WATER_BLOCK_TYPES[$block_id] = $block_id;
		}
	}

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(BlockLegacyIds::AIR, BlockLegacyIds::LEAVES);
		$this->setHeight($random->nextBoundedInt(4) + 5);
		$this->setType(TreeType::OAK());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT;
	}

	public function canPlace(int $base_x, int $base_y, int $base_z, ChunkManager $world) : bool{
		for($y = $base_y; $y <= $base_y + 1 + $this->height; ++$y){
			if($y < 0 || $y >= World::Y_MAX){ // height out of range
				return false;
			}

			// Space requirement
			$radius = 1; // default radius if above first block
			if($y === $base_y){
				$radius = 0; // radius at source block y is 0 (only trunk)
			}elseif($y >= $base_y + 1 + $this->height - 2){
				$radius = 3; // max radius starting at leaves bottom
			}
			// check for block collision on horizontal slices
			for($x = $base_x - $radius; $x <= $base_x + $radius; ++$x){
				for($z = $base_z - $radius; $z <= $base_z + $radius; ++$z){
					// we can overlap some blocks around
					$type = $world->getBlockAt($x, $y, $z)->getId();
					if(array_key_exists($type, $this->overridables)){
						continue;
					}

					if($type === BlockLegacyIds::FLOWING_WATER || $type === BlockLegacyIds::STILL_WATER){
						if($y > $base_y){
							return false;
						}
					}else{
						return false;
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		/** @var Chunk $chunk */
		$chunk = $world->getChunk($source_x >> 4, $source_z >> 4);
		$chunk_block_x = $source_x & 0x0f;
		$chunk_block_z = $source_z & 0x0f;
		$block_factory = BlockFactory::getInstance();
		while(array_key_exists($block_factory->fromFullBlock($chunk->getFullBlock($chunk_block_x, $source_y, $chunk_block_z))->getId(), self::$WATER_BLOCK_TYPES)){
			--$source_y;
		}

		++$source_y;
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		// generate the leaves
		for($y = $source_y + $this->height - 3; $y <= $source_y + $this->height; ++$y){
			$n = $y - ($source_y + $this->height);
			$radius = (int) (2 - $n / 2);
			for($x = $source_x - $radius; $x <= $source_x + $radius; ++$x){
				for($z = $source_z - $radius; $z <= $source_z + $radius; ++$z){
					if(
						abs($x - $source_x) !== $radius ||
						abs($z - $source_z) !== $radius ||
						($random->nextBoolean() && $n !== 0)
					){
						$this->replaceIfAirOrLeaves($x, $y, $z, $this->leaves_type, $world);
					}
				}
			}
		}

		$world_height = $world->getMaxY();
		// generate the trunk
		for($y = 0; $y < $this->height; ++$y){
			if($source_y + $y < $world_height){
				$material = $block_factory->fromFullBlock($chunk->getFullBlock($chunk_block_x, $source_y + $y, $chunk_block_z))->getId();
				if(
					$material === BlockLegacyIds::AIR ||
					$material === BlockLegacyIds::LEAVES ||
					$material === BlockLegacyIds::FLOWING_WATER ||
					$material === BlockLegacyIds::STILL_WATER
				){
					$this->transaction->addBlockAt($source_x, $source_y + $y, $source_z, $this->log_type);
				}
			}
		}

		// add some vines on the leaves
		$this->addVinesOnLeaves($source_x, $source_y, $source_z, $world, $random);

		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z, VanillaBlocks::DIRT());
		return true;
	}
}

SwampTree::init();