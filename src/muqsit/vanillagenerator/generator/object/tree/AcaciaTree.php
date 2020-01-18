<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class AcaciaTree extends GenericTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(3) + $random->nextBoundedInt(3) + 5);
		$this->setType(TreeType::ACACIA());
	}

	public function canPlaceOn(Block $soil) : bool{
		$type = $soil->getId();
		return $type === BlockLegacyIds::GRASS || $type === BlockLegacyIds::DIRT;
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		if($this->cannotGenerateAt($blockX, $blockY, $blockZ, $world)){
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
		$twistHeight = $this->height - 1 - $random->nextBoundedInt(4);
		$twistCount = $random->nextBoundedInt(3) + 1;
		$centerX = $blockX;
		$centerZ = $blockZ;
		$trunkTopY = 0;
		// generates the trunk
		for($y = 0; $y < $this->height; ++$y){

			// trunk twists
			if($twistCount > 0 && $y >= $twistHeight){
				$centerX += $dx;
				$centerZ += $dz;
				--$twistCount;
			}

			$material = $world->getBlockAt($centerX, $blockY + $y, $centerZ);
			if($material === BlockLegacyIds::AIR || $material === BlockLegacyIds::LEAVES){
				$trunkTopY = $blockY + $y;
				$this->transaction->addBlockAt($centerX, $blockY + $y, $centerZ, $this->logType);
			}
		}

		// generates leaves
		for($x = -3; $x <= 3; ++$x){
			for($z = -3; $z <= 3; ++$z){
				if(abs($x) < 3 || abs($z) < 3){
					$this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
				}
				if(abs($x) < 2 && abs($z) < 2){
					$this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
				}
				if((abs($x) === 2 && abs($z) === 0) || (abs($x) === 0 && abs($z) === 2)){
					$this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
				}
			}
		}

		// try to choose a different direction for second branching and canopy
		$d = $random->nextFloat() * M_PI * 2.0;
		$dxB = (int) (cos($d) + 1.5) - 1;
		$dzB = (int) (sin($d) + 1.5) - 1;
		if(abs($dxB) > 0 && abs($dzB) > 0){
			if($random->nextBoolean()){
				$dxB = 0;
			}else{
				$dzB = 0;
			}
		}
		if($dx !== $dxB || $dz !== $dzB){
			$centerX = $blockX;
			$centerZ = $blockZ;
			$branchHeight = $twistHeight - 1 - $random->nextBoundedInt(2);
			$twistCount = $random->nextBoundedInt(3) + 1;
			$trunkTopY = 0;

			// generates the trunk
			for($y = $branchHeight + 1; $y < $this->height; ++$y){
				if($twistCount > 0){
					$centerX += $dxB;
					$centerZ += $dzB;
					$material = $world->getBlockAt($centerX, $blockY + $y, $centerZ)->getId();
					if($material === BlockLegacyIds::AIR || $material === BlockLegacyIds::LEAVES){
						$trunkTopY = $blockY + $y;
						$this->transaction->addBlockAt($centerX, $blockY + $y, $centerZ, $this->logType);
					}
					--$twistCount;
				}
			}

			// generates the leaves
			if($trunkTopY > 0){
				for($x = -2; $x <= 2; ++$x){
					for($z = -2; $z <= 2; ++$z){
						if(abs($x) < 2 || abs($z) < 2){
							$this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
						}
					}
				}
				for($x = -1; $x <= 1; ++$x){
					for($z = -1; $z <= 1; ++$z){
						$this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
					}
				}
			}
		}

		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, VanillaBlocks::DIRT());

		return true;
	}

	private function setLeaves(int $x, int $y, int $z, ChunkManager $world) : void{
		if($world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR){
			$this->transaction->addBlockAt($x, $y, $z, $this->leavesType);
		}
	}
}