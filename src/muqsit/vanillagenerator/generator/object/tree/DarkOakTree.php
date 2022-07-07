<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class DarkOakTree extends GenericTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockTypeIds::AIR,
			BlockTypeIds::ACACIA_LEAVES,
			BlockTypeIds::BIRCH_LEAVES,
			BlockTypeIds::DARK_OAK_LEAVES,
			BlockTypeIds::JUNGLE_LEAVES,
			BlockTypeIds::OAK_LEAVES,
			BlockTypeIds::SPRUCE_LEAVES,
			BlockTypeIds::GRASS,
			BlockTypeIds::DIRT,
			BlockTypeIds::ACACIA_WOOD,
			BlockTypeIds::BIRCH_WOOD,
			BlockTypeIds::DARK_OAK_WOOD,
			BlockTypeIds::JUNGLE_WOOD,
			BlockTypeIds::OAK_WOOD,
			BlockTypeIds::SPRUCE_WOOD,
			BlockTypeIds::ACACIA_SAPLING,
			BlockTypeIds::BIRCH_SAPLING,
			BlockTypeIds::DARK_OAK_SAPLING,
			BlockTypeIds::JUNGLE_SAPLING,
			BlockTypeIds::OAK_SAPLING,
			BlockTypeIds::SPRUCE_SAPLING,
			BlockTypeIds::VINES
		);

		$this->setHeight($random->nextBoundedInt(2) + $random->nextBoundedInt(3) + 6);
		$this->setType(VanillaBlocks::DARK_OAK_LOG(), VanillaBlocks::DARK_OAK_LEAVES());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getTypeId();
		return $id === BlockTypeIds::GRASS || $id === BlockTypeIds::DIRT;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		$d = $random->nextFloat() * M_PI * 2.0; // random direction
		$dx = (int) (cos($d) + 1.5) - 1;
		$dz = (int) (sin($d) + 1.5) - 1;
		if(abs($dx) > 0 && abs($dz) > 0){ // reduce possible directions to NESW
			if($random->nextBoolean()){
				$dx = 0;
			}else{
				$dz = 0;
			}
		}
		$twist_height = $this->height - $random->nextBoundedInt(4);
		$twist_count = $random->nextBoundedInt(3);
		$center_x = $source_x;
		$center_z = $source_z;
		$trunk_top_y = 0;

		// generates the trunk
		for($y = 0; $y < $this->height; ++$y){

			// trunk twists
			if($twist_count > 0 && $y >= $twist_height){
				$center_x += $dx;
				$center_z += $dz;
				--$twist_count;
			}

			$material = $world->getBlockAt($center_x, $source_y + $y, $center_z);
			if($material->getTypeId() !== BlockTypeIds::AIR && !($material instanceof Leaves)){
				continue;
			}
			$trunk_top_y = $source_y + $y;
			// SELF, SOUTH, EAST, SOUTH EAST
			$this->transaction->addBlockAt($center_x, $source_y + $y, $center_z, $this->log_type);
			$this->transaction->addBlockAt($center_x, $source_y + $y, $center_z + 1, $this->log_type);
			$this->transaction->addBlockAt($center_x + 1, $source_y + $y, $center_z, $this->log_type);
			$this->transaction->addBlockAt($center_x + 1, $source_y + $y, $center_z + 1, $this->log_type);
		}

		// generates leaves
		for($x = -2; $x <= 0; ++$x){
			for($z = -2; $z <= 0; ++$z){
				if(($x !== -1 || $z !== -2) && ($x > -2 || $z > -1)){
					$this->setLeaves($center_x + $x, $trunk_top_y + 1, $center_z + $z, $world);
					$this->setLeaves(1 + $center_x - $x, $trunk_top_y + 1, $center_z + $z, $world);
					$this->setLeaves($center_x + $x, $trunk_top_y + 1, 1 + $center_z - $z, $world);
					$this->setLeaves(1 + $center_x - $x, $trunk_top_y + 1, 1 + $center_z - $z, $world);
				}
				$this->setLeaves($center_x + $x, $trunk_top_y - 1, $center_z + $z, $world);
				$this->setLeaves(1 + $center_x - $x, $trunk_top_y - 1, $center_z + $z, $world);
				$this->setLeaves($center_x + $x, $trunk_top_y - 1, 1 + $center_z - $z, $world);
				$this->setLeaves(1 + $center_x - $x, $trunk_top_y - 1, 1 + $center_z - $z, $world);
			}
		}

		// finish leaves below the canopy
		for($x = -3; $x <= 4; ++$x){
			for($z = -3; $z <= 4; ++$z){
				if(abs($x) < 3 || abs($z) < 3){
					$this->setLeaves($center_x + $x, $trunk_top_y, $center_z + $z, $world);
				}
			}
		}

		// generates some trunk excrescences
		for($x = -1; $x <= 2; ++$x){
			for($z = -1; $z <= 2; ++$z){
				if(($x !== -1 && $z !== -1 && $x !== 2 && $z !== 2) || $random->nextBoundedInt(3) !== 0){
					continue;
				}
				for($y = 0; $y < $random->nextBoundedInt(3) + 2; ++$y){
					$material = $world->getBlockAt($source_x + $x, $trunk_top_y - $y - 1, $source_z + $z);
					if($material->getTypeId() === BlockTypeIds::AIR || $material instanceof Leaves){
						$this->transaction->addBlockAt($source_x + $x, $trunk_top_y - $y - 1, $source_z + $z, $this->log_type);
					}
				}

				// leaves below the canopy
				for($i = -1; $i <= 1; ++$i){
					for($j = -1; $j <= 1; ++$j){
						$this->setLeaves($center_x + $x + $i, $trunk_top_y, $center_z + $z + $j, $world);
					}
				}
				for($i = -2; $i <= 2; ++$i){
					for($j = -2; $j <= 2; ++$j){
						if(abs($i) < 2 || abs($j) < 2){
							$this->setLeaves($center_x + $x + $i, $trunk_top_y - 1, $center_z + $z + $j, $world);
						}
					}
				}
			}
		}

		// 50% chance to have a 4 leaves cap on the center of the canopy
		if($random->nextBoundedInt(2) === 0){
			$this->setLeaves($center_x, $trunk_top_y + 2, $center_z, $world);
			$this->setLeaves($center_x + 1, $trunk_top_y + 2, $center_z, $world);
			$this->setLeaves($center_x + 1, $trunk_top_y + 2, $center_z + 1, $world);
			$this->setLeaves($center_x, $trunk_top_y + 2, $center_z + 1, $world);
		}

		// block below trunk is always dirt (SELF, SOUTH, EAST, SOUTH EAST)
		$dirt = VanillaBlocks::DIRT();
		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z, $dirt);
		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z + 1, $dirt);
		$this->transaction->addBlockAt($source_x + 1, $source_y - 1, $source_z, $dirt);
		$this->transaction->addBlockAt($source_x + 1, $source_y - 1, $source_z + 1, $dirt);
		return true;
	}

	private function setLeaves(int $x, int $y, int $z, ChunkManager $world) : void{
		if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR){
			$this->transaction->addBlockAt($x, $y, $z, $this->leaves_type);
		}
	}
}