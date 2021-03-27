<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function array_key_exists;

class BrownMushroomTree extends GenericTree{

	/** @var int */
	protected int $type = BlockLegacyIds::BROWN_MUSHROOM_BLOCK;

	/**
	 * Initializes this mushroom with a random height, preparing it to attempt to generate.
	 *
	 * @param Random $random
	 * @param BlockTransaction $transaction
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockLegacyIds::AIR,
			BlockLegacyIds::LEAVES,
			BlockLegacyIds::LEAVES2
		);
		$this->setHeight($random->nextBoundedInt(3) + 4);
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT || $id === BlockLegacyIds::MYCELIUM;
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
						if(!array_key_exists($world->getBlockAt($x, $y, $z)->getId(), $this->overridables)){
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

		$block_factory = BlockFactory::getInstance();

		// generate the stem
		$stem = $block_factory->get($this->type, 10);
		for($y = 0; $y < $this->height; ++$y){
			$this->transaction->addBlockAt($source_x, $source_y + $y, $source_z, $stem); // stem texture
		}

		// get the mushroom's cap Y start
		$cap_y = $source_y + $this->height; // for brown mushroom it starts on top directly
		if($this->type === BlockLegacyIds::RED_MUSHROOM_BLOCK){
			$cap_y = $source_y + $this->height - 3; // for red mushroom, cap's thickness is 4 blocks
		}

		// generate mushroom's cap
		for($y = $cap_y; $y <= $source_y + $this->height; ++$y){ // from bottom to top of mushroom
			$radius = 1; // radius for the top of red mushroom
			if($y < $source_y + $this->height){
				$radius = 2; // radius for red mushroom cap is 2
			}
			if($this->type === BlockLegacyIds::BROWN_MUSHROOM_BLOCK){
				$radius = 3; // radius always 3 for a brown mushroom
			}
			// loop over horizontal slice
			for($x = $source_x - $radius; $x <= $source_x + $radius; ++$x){
				for($z = $source_z - $radius; $z <= $source_z + $radius; ++$z){
					$data = 5; // cap texture on top
					// cap's borders/corners treatment
					if($x === $source_x - $radius){
						$data = 4; // cap texture on top and west
					}elseif($x === $source_x + $radius){
						$data = 6; // cap texture on top and east
					}
					if($z === $source_z - $radius){
						$data -= 3;
					}elseif($z === $source_z + $radius){
						$data += 3;
					}

					// corners shrink treatment
					// if it's a brown mushroom we need it always
					// it's a red mushroom, it's only applied below the top
					if($this->type === BlockLegacyIds::BROWN_MUSHROOM_BLOCK || $y < $source_y + $this->height){

						// excludes the real corners of the cap structure
						if(($x === $source_x - $radius || $x === $source_x + $radius)
							&& ($z === $source_z - $radius || $z === $source_z + $radius)){
							continue;
						}

						// mushroom's cap corners treatment
						if($x === $source_x - ($radius - 1) && $z === $source_z - $radius){
							$data = 1; // cap texture on top, west and north
						}elseif($x === $source_x - $radius && $z === $source_z - ($radius
								- 1)){
							$data = 1; // cap texture on top, west and north
						}elseif($x === $source_x + $radius - 1 && $z === $source_z - $radius){
							$data = 3; // cap texture on top, north and east
						}elseif($x === $source_x + $radius && $z === $source_z - ($radius - 1)){
							$data = 3; // cap texture on top, north and east
						}elseif($x === $source_x - ($radius - 1) && $z === $source_z + $radius){
							$data = 7; // cap texture on top, south and west
						}elseif($x === $source_x - $radius && $z === $source_z + $radius - 1){
							$data = 7; // cap texture on top, south and west
						}elseif($x === $source_x + $radius - 1 && $z === $source_z + $radius){
							$data = 9; // cap texture on top, east and south
						}elseif($x === $source_x + $radius && $z === $source_z + $radius - 1){
							$data = 9; // cap texture on top, east and south
						}
					}

					// a $data of 5 below the top layer means air
					if($data !== 5 || $y >= $source_y + $this->height){
						$this->transaction->addBlockAt($x, $y, $z, $block_factory->get($this->type, $data));
					}
				}
			}
		}

		return true;
	}
}