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

class AcaciaTree extends GenericTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(3) + $random->nextBoundedInt(3) + 5);
		$this->setType(VanillaBlocks::ACACIA_LOG(), VanillaBlocks::ACACIA_LEAVES());
	}

	public function canPlaceOn(Block $soil) : bool{
		$type = $soil->getTypeId();
		return $type === BlockTypeIds::GRASS || $type === BlockTypeIds::DIRT;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		$d = ($random->nextFloat() * M_PI * 2.0); // random direction
		$dx = (int) (cos($d) + 1.5) - 1;
		$dz = (int) (sin($d) + 1.5) - 1;
		if(abs($dx) > 0 && abs($dz) > 0){ // reduce possible directions to NESW
			if($random->nextBoolean()){
				$dx = 0;
			}else{
				$dz = 0;
			}
		}
		$twist_height = $this->height - 1 - $random->nextBoundedInt(4);
		$twist_count = $random->nextBoundedInt(3) + 1;
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
			if($material->getTypeId() === BlockTypeIds::AIR || $material instanceof Leaves){
				$trunk_top_y = $source_y + $y;
				$this->transaction->addBlockAt($center_x, $source_y + $y, $center_z, $this->log_type);
			}
		}

		// generates leaves
		for($x = -3; $x <= 3; ++$x){
			$abs_x = abs($x);
			for($z = -3; $z <= 3; ++$z){
				$abs_z = abs($z);
				if($abs_x < 3 || $abs_z < 3){
					$this->setLeaves($center_x + $x, $trunk_top_y, $center_z + $z, $world);
				}
				if($abs_x < 2 && $abs_z < 2){
					$this->setLeaves($center_x + $x, $trunk_top_y + 1, $center_z + $z, $world);
				}
				if(($abs_x === 2 && $abs_z === 0) || ($abs_x === 0 && $abs_z === 2)){
					$this->setLeaves($center_x + $x, $trunk_top_y + 1, $center_z + $z, $world);
				}
			}
		}

		// try to choose a different direction for second branching and canopy
		$d = $random->nextFloat() * M_PI * 2.0;
		$dx_b = (int) (cos($d) + 1.5) - 1;
		$dz_b = (int) (sin($d) + 1.5) - 1;
		if(abs($dx_b) > 0 && abs($dz_b) > 0){
			if($random->nextBoolean()){
				$dx_b = 0;
			}else{
				$dz_b = 0;
			}
		}
		if($dx !== $dx_b || $dz !== $dz_b){
			$center_x = $source_x;
			$center_z = $source_z;
			$branch_height = $twist_height - 1 - $random->nextBoundedInt(2);
			$twist_count = $random->nextBoundedInt(3) + 1;
			$trunk_top_y = 0;

			// generates the trunk
			for($y = $branch_height + 1; $y < $this->height; ++$y){
				if($twist_count > 0){
					$center_x += $dx_b;
					$center_z += $dz_b;
					$material = $world->getBlockAt($center_x, $source_y + $y, $center_z);
					if($material->getTypeId() === BlockTypeIds::AIR || $material instanceof Leaves){
						$trunk_top_y = $source_y + $y;
						$this->transaction->addBlockAt($center_x, $source_y + $y, $center_z, $this->log_type);
					}
					--$twist_count;
				}
			}

			// generates the leaves
			if($trunk_top_y > 0){
				for($x = -2; $x <= 2; ++$x){
					for($z = -2; $z <= 2; ++$z){
						if(abs($x) < 2 || abs($z) < 2){
							$this->setLeaves($center_x + $x, $trunk_top_y, $center_z + $z, $world);
						}
					}
				}
				for($x = -1; $x <= 1; ++$x){
					for($z = -1; $z <= 1; ++$z){
						$this->setLeaves($center_x + $x, $trunk_top_y + 1, $center_z + $z, $world);
					}
				}
			}
		}

		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z, VanillaBlocks::DIRT());

		return true;
	}

	private function setLeaves(int $x, int $y, int $z, ChunkManager $world) : void{
		if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR){
			$this->transaction->addBlockAt($x, $y, $z, $this->leaves_type);
		}
	}
}