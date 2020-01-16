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
use pocketmine\world\World;

class SwampTree extends CocoaTree{

	public const WATER_BLOCK_TYPES = [BlockLegacyIds::WATER, BlockLegacyIds::STILL_WATER];

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(BlockLegacyIds::AIR, BlockLegacyIds::LEAVES);
		$this->setHeight($random->nextBoundedInt(4) + 5);
		$this->setType(TreeType::OAK());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			if($y < 0 || $y >= World::Y_MAX){ // height out of range
				return false;
			}

			// Space requirement
			$radius = 1; // default radius if above first block
			if($y === $baseY){
				$radius = 0; // radius at source block y is 0 (only trunk)
			}elseif($y >= $baseY + 1 + $this->height - 2){
				$radius = 3; // max radius starting at leaves bottom
			}
			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					// we can overlap some blocks around
					$type = $world->getBlockAt($x, $y, $z)->getId();
					if(isset($this->overridables[$type])){
						continue;
					}

					if($type === BlockLegacyIds::WATER || $type === BlockLegacyIds::STILL_WATER){
						if($y > $baseY){
							return false;
						}
					}else{
						return false;
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		while(in_array($world->getBlockAt($blockX, $blockY, $blockZ)->getId(), self::WATER_BLOCK_TYPES, true)){
			--$blockY;
		}

		if(!$this->cannotGenerateAt($blockX, $blockY, $blockZ, $world)){
			return false;
		}

		// generate the leaves
		for($y = $blockY + $this->height - 3; $y <= $blockY + $this->height; ++$y){
			$n = $y - ($blockY + $this->height);
			$radius = 2 - $n / 2;
			for($x = $blockX - $radius; $x <= $blockX + $radius; ++$x){
				for($z = $blockZ - $radius; $z <= $blockZ + $radius; ++$z){
					if(
						abs($x - $blockX) !== $radius ||
						abs($z - $blockZ) !== $radius ||
						($random->nextBoolean() && $n !== 0)
					){
						$this->replaceIfAirOrLeaves((int) $x, $y, (int) $z, $this->leavesType, $world);
					}
				}
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height; ++$y){
			$material = $world->getBlockAt($blockX, $blockY + $y, $blockZ)->getId();
			if(
				$material === BlockLegacyIds::AIR ||
				$material === BlockLegacyIds::LEAVES ||
				$material === BlockLegacyIds::WATER ||
				$material === BlockLegacyIds::STILL_WATER
			){
				$this->transaction->addBlockAt($blockX, $blockY + $y, $blockZ, $this->logType);
			}
		}

		// add some vines on the leaves
		$this->addVinesOnLeaves($blockX, $blockY, $blockZ, $world, $random);

		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, VanillaBlocks::DIRT());
		return true;
	}
}