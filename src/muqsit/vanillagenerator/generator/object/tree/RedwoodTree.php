<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;

class RedwoodTree extends GenericTree{

	/** @var int */
	protected $maxRadius;

	/** @var int */
	protected $leavesHeight;

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockLegacyIds::AIR,
			BlockLegacyIds::LEAVES
		);
		$this->setHeight($random->nextBoundedInt(4) + 6);
		$this->setLeavesHeight($random->nextBoundedInt(2) + 1);
		$this->setMaxRadius($random->nextBoundedInt(2) + 2);
		$this->setType(TreeType::SPRUCE());
	}

	final protected function setMaxRadius(int $maxRadius) : void{
		$this->maxRadius = $maxRadius;
	}

	final protected function setLeavesHeight(int $leavesHeight) : void{
		$this->leavesHeight = $leavesHeight;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			if($y - $baseY < $this->leavesHeight){
				$radius = 0; // radius is 0 for trunk below leaves
			}else{
				$radius = $this->maxRadius;
			}
			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					if($y >= 0 && $y < World::Y_MAX){
						// we can overlap some blocks around
						$type = $world->getBlockAt($x, $y, $z)->getId();
						if(!isset($this->overridables[$type])){
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

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		if($this->cannotGenerateAt($blockX, $blockY, $blockZ, $world)){
			return false;
		}

		// generate the leaves
		$radius = $random->nextBoundedInt(2);
		$peakRadius = 1;
		$minRadius = 0;
		for($y = $blockY + $this->height; $y >= $blockY + $this->leavesHeight; --$y){
			// leaves are built from top to bottom
			for($x = $blockX - $radius; $x <= $blockX + $radius; ++$x){
				for($z = $blockZ - $radius; $z <= $blockZ + $radius; ++$z){
					if(
						(
							abs($x - $blockX) !== $radius ||
							abs($z - $blockZ) !== $radius ||
							$radius <= 0
						) &&
						$world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR
					){
						$this->transaction->addBlockAt($x, $y, $z, $this->leavesType);
					}
				}
			}
			if($radius >= $peakRadius){
				$radius = $minRadius;
				$minRadius = 1; // after the peak $radius is reached once, the min $radius increases
				++$peakRadius;  // the peak $radius increases each time it's reached
				if($peakRadius > $this->maxRadius){
					$peakRadius = $this->maxRadius;
				}
			}else{
				++$radius;
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height - $random->nextBoundedInt(3); $y++){
			$type = $world->getBlockAt($blockX, $blockY + $y, $blockZ)->getId();
			if(isset($this->overridables[$type])){
				$this->transaction->addBlockAt($blockX, $blockY + $y, $blockZ, $this->logType);
			}
		}

		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, VanillaBlocks::DIRT());
		return true;
	}
}