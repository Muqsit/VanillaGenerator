<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use function array_key_exists;

class RedwoodTree extends GenericTree{

	protected int $max_radius;
	protected int $leaves_height;

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
		$this->setHeight($random->nextBoundedInt(4) + 6);
		$this->setLeavesHeight($random->nextBoundedInt(2) + 1);
		$this->setMaxRadius($random->nextBoundedInt(2) + 2);
		$this->setType(VanillaBlocks::SPRUCE_LOG(), VanillaBlocks::SPRUCE_LEAVES());
	}

	final protected function setMaxRadius(int $max_radius) : void{
		$this->max_radius = $max_radius;
	}

	final protected function setLeavesHeight(int $leaves_height) : void{
		$this->leaves_height = $leaves_height;
	}

	public function canPlace(int $base_x, int $base_y, int $base_z, ChunkManager $world) : bool{
		for($y = $base_y; $y <= $base_y + 1 + $this->height; ++$y){
			if($y - $base_y < $this->leaves_height){
				$radius = 0; // radius is 0 for trunk below leaves
			}else{
				$radius = $this->max_radius;
			}
			// check for block collision on horizontal slices
			for($x = $base_x - $radius; $x <= $base_x + $radius; ++$x){
				for($z = $base_z - $radius; $z <= $base_z + $radius; ++$z){
					if($y >= 0 && $y < World::Y_MAX){
						// we can overlap some blocks around
						$type = $world->getBlockAt($x, $y, $z)->getTypeId();
						if(!array_key_exists($type, $this->overridables)){
							return false;
						}
					}else{ // $this->height out of range
						return false;
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

		// generate the leaves
		$radius = $random->nextBoundedInt(2);
		$peak_radius = 1;
		$min_radius = 0;
		for($y = $source_y + $this->height; $y >= $source_y + $this->leaves_height; --$y){
			// leaves are built from top to bottom
			for($x = $source_x - $radius; $x <= $source_x + $radius; ++$x){
				for($z = $source_z - $radius; $z <= $source_z + $radius; ++$z){
					if(
						(
							abs($x - $source_x) !== $radius ||
							abs($z - $source_z) !== $radius ||
							$radius <= 0
						) &&
						$world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR
					){
						$this->transaction->addBlockAt($x, $y, $z, $this->leaves_type);
					}
				}
			}
			if($radius >= $peak_radius){
				$radius = $min_radius;
				$min_radius = 1; // after the peak $radius is reached once, the min $radius increases
				++$peak_radius;  // the peak $radius increases each time it's reached
				if($peak_radius > $this->max_radius){
					$peak_radius = $this->max_radius;
				}
			}else{
				++$radius;
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height - $random->nextBoundedInt(3); $y++){
			$type = $world->getBlockAt($source_x, $source_y + $y, $source_z)->getTypeId();
			if(array_key_exists($type, $this->overridables)){
				$this->transaction->addBlockAt($source_x, $source_y + $y, $source_z, $this->log_type);
			}
		}

		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z, VanillaBlocks::DIRT());
		return true;
	}
}