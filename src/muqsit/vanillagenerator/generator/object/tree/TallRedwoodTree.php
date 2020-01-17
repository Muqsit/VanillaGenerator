<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class TallRedwoodTree extends RedwoodTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockLegacyIds::AIR,
			BlockLegacyIds::LEAVES,
			BlockLegacyIds::GRASS,
			BlockLegacyIds::DIRT,
			BlockLegacyIds::LOG,
			BlockLegacyIds::LOG2,
			BlockLegacyIds::SAPLING,
			BlockLegacyIds::VINE
		);
		$this->setHeight($random->nextBoundedInt(5) + 7);
		$this->setLeavesHeight($this->height - $random->nextBoundedInt(2) - 3);
		$this->setMaxRadius($random->nextBoundedInt($this->height - $this->leavesHeight + 1) + 1);
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		if($this->cannotGenerateAt($blockX, $blockY, $blockZ, $world)){
			return false;
		}

		// generate the leaves
		$radius = 0;
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
			if($radius >= 1 && $y === $blockY + $this->leavesHeight + 1){
				--$radius;
			}elseif($radius < $this->maxRadius){
				++$radius;
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height - 1; ++$y){
			$this->replaceIfAirOrLeaves($blockX, $blockY + $y, $blockZ, $this->logType, $world);
		}

		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, VanillaBlocks::DIRT());
		return true;
	}
}