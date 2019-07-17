<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\utils\TreeType;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class DarkOakTree extends GenericTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockLegacyIds::AIR,
			BlockLegacyIds::LEAVES,
			BlockLegacyIds::LEAVES2,
			BlockLegacyIds::GRASS,
			BlockLegacyIds::DIRT,
			BlockLegacyIds::LOG,
			BlockLegacyIds::LOG2,
			BlockLegacyIds::SAPLING,
			BlockLegacyIds::VINE
		);

		$this->setHeight($random->nextBoundedInt(2) + $random->nextBoundedInt(3) + 6);
		$this->setType(TreeType::DARK_OAK());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT;
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		if($this->cannotGenerateAt($blockX, $blockY, $blockZ, $world)){
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
		$twistHeight = $this->height - $random->nextBoundedInt(4);
		$twistCount = $random->nextBoundedInt(3);
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

			$material = $world->getBlockAt($centerX, $blockY + $y, $centerZ)->getId();
			if($material !== BlockLegacyIds::AIR && $material !== BlockLegacyIds::LEAVES){
				continue;
			}
			$trunkTopY = $blockY + $y;
			// SELF, SOUTH, EAST, SOUTH EAST
			$this->transaction->addBlockAt($centerX, $blockY + $y, $centerZ, $this->logType);
			$this->transaction->addBlockAt($centerX, $blockY + $y, $centerZ + 1, $this->logType);
			$this->transaction->addBlockAt($centerX + 1, $blockY + $y, $centerZ, $this->logType);
			$this->transaction->addBlockAt($centerX + 1, $blockY + $y, $centerZ + 1, $this->logType);
		}

		// generates leaves
		for($x = -2; $x <= 0; ++$x){
			for($z = -2; $z <= 0; ++$z){
				if(($x !== -1 || $z !== -2) && ($x > -2 || $z > -1)){
					$this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
					$this->setLeaves(1 + $centerX - $x, $trunkTopY + 1, $centerZ + $z, $world);
					$this->setLeaves($centerX + $x, $trunkTopY + 1, 1 + $centerZ - $z, $world);
					$this->setLeaves(1 + $centerX - $x, $trunkTopY + 1, 1 + $centerZ - $z, $world);
				}
				$this->setLeaves($centerX + $x, $trunkTopY - 1, $centerZ + $z, $world);
				$this->setLeaves(1 + $centerX - $x, $trunkTopY - 1, $centerZ + $z, $world);
				$this->setLeaves($centerX + $x, $trunkTopY - 1, 1 + $centerZ - $z, $world);
				$this->setLeaves(1 + $centerX - $x, $trunkTopY - 1, 1 + $centerZ - $z, $world);
			}
		}

		// finish leaves below the canopy
		for($x = -3; $x <= 4; ++$x){
			for($z = -3; $z <= 4; ++$z){
				if(abs($x) < 3 || abs($z) < 3){
					$this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
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
					$material = $world->getBlockAt($blockX + $x, $trunkTopY - $y - 1, $blockZ + $z)->getId();
					if($material === BlockLegacyIds::AIR || $material === BlockLegacyIds::LEAVES){
						$this->transaction->addBlockAt($blockX + $x, $trunkTopY - $y - 1, $blockZ + $z, $this->logType);
					}
				}

				// leaves below the canopy
				for($i = -1; $i <= 1; ++$i){
					for($j = -1; $j <= 1; ++$j){
						$this->setLeaves($centerX + $x + $i, $trunkTopY, $centerZ + $z + $j, $world);
					}
				}
				for($i = -2; $i <= 2; ++$i){
					for($j = -2; $j <= 2; ++$j){
						if(abs($i) < 2 || abs($j) < 2){
							$this->setLeaves($centerX + $x + $i, $trunkTopY - 1, $centerZ + $z + $j, $world);
						}
					}
				}
			}
		}

		// 50% chance to have a 4 leaves cap on the center of the canopy
		if($random->nextBoundedInt(2) === 0){
			$this->setLeaves($centerX, $trunkTopY + 2, $centerZ, $world);
			$this->setLeaves($centerX + 1, $trunkTopY + 2, $centerZ, $world);
			$this->setLeaves($centerX + 1, $trunkTopY + 2, $centerZ + 1, $world);
			$this->setLeaves($centerX, $trunkTopY + 2, $centerZ + 1, $world);
		}

		// block below trunk is always dirt (SELF, SOUTH, EAST, SOUTH EAST)
		$dirt = BlockFactory::get(BlockLegacyIds::DIRT, BlockLegacyMetadata::DIRT_NORMAL);
		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, $dirt);
		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ + 1, $dirt);
		$this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ, $dirt);
		$this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ + 1, $dirt);
		return true;
	}

	private function setLeaves(int $x, int $y, int $z, ChunkManager $world) : void{
		if($world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR){
			$this->transaction->addBlockAt($x, $y, $z, $this->leavesType);
		}
	}
}