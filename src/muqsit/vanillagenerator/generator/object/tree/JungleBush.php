<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class JungleBush extends GenericTree{

	/**
	 * Initializes this bush, preparing it to attempt to generate.
	 * @param Random $random
	 * @param BlockTransaction $transaction
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setType(TreeType::JUNGLE());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT;
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		while((($blockId = $world->getBlockAt($blockX, $blockY, $blockZ)->getId()) === BlockLegacyIds::AIR || $blockId === BlockLegacyIds::LEAVES) && $blockY > 0){
			--$blockY;
		}

		// check only below block
		if(!$this->canPlaceOn($world->getBlockAt($blockX, $blockY - 1, $blockZ))){
			return false;
		}

		// generates the trunk
		$adjustedY = $blockY;
		$this->transaction->addBlockAt($blockX, $adjustedY + 1, $blockZ, $this->logType);

		// generates the leaves
		for($y = $adjustedY + 1; $y <= $adjustedY + 3; ++$y){
			$radius = 3 - ($y - $adjustedY);

			for($x = $blockX - $radius; $x <= $blockX + $radius; ++$x){
				for($z = $blockZ - $radius; $z <= $blockZ + $radius; ++$z){
					if(
						!$this->transaction->fetchBlockAt($x, $y, $z)->isSolid() &&
						(
							abs($x - $blockX) !== $radius ||
							abs($z - $blockZ) !== $radius ||
							$random->nextBoolean()
						)
					){
						$this->transaction->addBlockAt($x, $y, $z, $this->leavesType);
					}
				}
			}
		}

		return true;
	}
}