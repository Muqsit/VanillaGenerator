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
		$this->setMaxRadius($random->nextBoundedInt($this->height - $this->leaves_height + 1) + 1);
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		// generate the leaves
		$radius = 0;
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
						$world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR
					){
						$this->transaction->addBlockAt($x, $y, $z, $this->leaves_type);
					}
				}
			}
			if($radius >= 1 && $y === $source_y + $this->leaves_height + 1){
				--$radius;
			}elseif($radius < $this->max_radius){
				++$radius;
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height - 1; ++$y){
			$this->replaceIfAirOrLeaves($source_x, $source_y + $y, $source_z, $this->log_type, $world);
		}

		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z, VanillaBlocks::DIRT());
		return true;
	}
}