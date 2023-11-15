<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function array_key_exists;

class BrownMushroomTree extends GenericTree{

	/**
	 * Initializes this mushroom with a random height, preparing it to attempt to generate.
	 *
	 * @param Random $random
	 * @param BlockTransaction $transaction
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockTypeIds::AIR,
			BlockTypeIds::ACACIA_LEAVES,
			BlockTypeIds::BIRCH_LEAVES,
			BlockTypeIds::DARK_OAK_LEAVES,
			BlockTypeIds::JUNGLE_LEAVES,
			BlockTypeIds::OAK_LEAVES,
			BlockTypeIds::SPRUCE_LEAVES
		);
		$this->setHeight($random->nextBoundedInt(3) + 4);
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getTypeId();
		return $id === BlockTypeIds::GRASS || $id === BlockTypeIds::DIRT || $id === BlockTypeIds::MYCELIUM;
	}

	protected function getType() : RedMushroomBlock{
		return VanillaBlocks::BROWN_MUSHROOM_BLOCK();
	}

	public function canPlace(int $base_x, int $base_y, int $base_z, ChunkManager $world) : bool{
		$world_height = $world->getMaxY();
		for($y = $base_y; $y <= $base_y + 1 + $this->height; ++$y){
			// Space requirement is 7x7 blocks, so brown mushroom's cap
			// can be directly touching a mushroom next to it.
			// Since red mushrooms fits in 5x5 blocks it will never
			// touch another huge mushroom.
			$radius = 3;
			if($y <= $base_y + 3){
				$radius = 0; // radius is 0 below 4 blocks tall (only the stem to take in account)
			}

			// check for block collision on horizontal slices
			for($x = $base_x - $radius; $x <= $base_x + $radius; ++$x){
				for($z = $base_z - $radius; $z <= $base_z + $radius; ++$z){
					if($y < 0 || $y >= $world_height){ // height out of range
						return false;
					}
					// skip source block check
					if($y !== $base_y || $x !== $base_x || $z !== $base_z){
						// we can overlap leaves around
						if(!array_key_exists($world->getBlockAt($x, $y, $z)->getTypeId(), $this->overridables)){
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		// generate the stem
		$stem = VanillaBlocks::MUSHROOM_STEM();
		for($y = 0; $y < $this->height; ++$y){
			$this->transaction->addBlockAt($source_x, $source_y + $y, $source_z, $stem);
		}

		$type_id = $this->getType()->getTypeId();
		// get the mushroom's cap Y start
		$cap_y = $source_y + $this->height; // for brown mushroom it starts on top directly
		if($type_id === BlockTypeIds::RED_MUSHROOM_BLOCK){
			$cap_y = $source_y + $this->height - 3; // for red mushroom, cap's thickness is 4 blocks
		}

		// generate mushroom's cap
		for($y = $cap_y; $y <= $source_y + $this->height; ++$y){ // from bottom to top of mushroom
			$radius = match(true){
				$y < $source_y + $this->height => 2, // radius for red mushroom cap is 2
				$type_id === BlockTypeIds::BROWN_MUSHROOM_BLOCK => 3, // radius always 3 for a brown mushroom
				default => 1 // radius for the top of red mushroom
			};

			// loop over horizontal slice
			for($x = $source_x - $radius; $x <= $source_x + $radius; ++$x){
				for($z = $source_z - $radius; $z <= $source_z + $radius; ++$z){
					// cap's borders/corners treatment
					$data = match(true){
						$x === $source_x - $radius => match(true){
							$z === $source_z - $radius => MushroomBlockType::CAP_NORTHWEST,
							$z === $source_z + $radius => MushroomBlockType::CAP_SOUTHWEST,
							default => MushroomBlockType::CAP_WEST
						},
						$x === $source_x + $radius => match(true){
							$z === $source_z - $radius => MushroomBlockType::CAP_NORTHEAST,
							$z === $source_z + $radius => MushroomBlockType::CAP_SOUTHEAST,
							default => MushroomBlockType::CAP_EAST
						},
						default => match(true){
							$z === $source_z - $radius => MushroomBlockType::CAP_NORTH,
							$z === $source_z + $radius => MushroomBlockType::CAP_SOUTH,
							default => MushroomBlockType::CAP_MIDDLE
						}
					};

					// corners shrink treatment
					// if it's a brown mushroom we need it always
					// it's a red mushroom, it's only applied below the top
					if($type_id === BlockTypeIds::BROWN_MUSHROOM_BLOCK || $y < $source_y + $this->height){

						// excludes the real corners of the cap structure
						if(($x === $source_x - $radius || $x === $source_x + $radius)
							&& ($z === $source_z - $radius || $z === $source_z + $radius)){
							continue;
						}

						// mushroom's cap corners treatment
						if($x === $source_x - ($radius - 1) && $z === $source_z - $radius){
							$data = MushroomBlockType::CAP_NORTHWEST;
						}elseif($x === $source_x - $radius && $z === $source_z - ($radius - 1)){
							$data = MushroomBlockType::CAP_NORTHWEST;
						}elseif($x === $source_x + $radius - 1 && $z === $source_z - $radius){
							$data = MushroomBlockType::CAP_NORTHEAST;
						}elseif($x === $source_x + $radius && $z === $source_z - ($radius - 1)){
							$data = MushroomBlockType::CAP_NORTHEAST;
						}elseif($x === $source_x - ($radius - 1) && $z === $source_z + $radius){
							$data = MushroomBlockType::CAP_SOUTHWEST;
						}elseif($x === $source_x - $radius && $z === $source_z + $radius - 1){
							$data = MushroomBlockType::CAP_SOUTHWEST;
						}elseif($x === $source_x + $radius - 1 && $z === $source_z + $radius){
							$data = MushroomBlockType::CAP_SOUTHEAST;
						}elseif($x === $source_x + $radius && $z === $source_z + $radius - 1){
							$data = MushroomBlockType::CAP_SOUTHEAST;
						}
					}

					// a $data of CAP_MIDDLE below the top layer means air
					if($data !== MushroomBlockType::CAP_MIDDLE || $y >= $source_y + $this->height){
						$this->transaction->addBlockAt($x, $y, $z, $this->getType()->setMushroomBlockType($data));
					}
				}
			}
		}

		return true;
	}
}