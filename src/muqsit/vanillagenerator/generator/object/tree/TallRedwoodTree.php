<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class TallRedwoodTree extends RedwoodTree{

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
						$world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR
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