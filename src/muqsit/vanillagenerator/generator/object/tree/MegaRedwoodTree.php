<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\utils\TreeType;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class MegaRedwoodTree extends MegaJungleTree{

	/** @var int */
	protected $leavesHeight;

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(15) + $random->nextBoundedInt(3) + 13);
		$this->setType(TreeType::SPRUCE());
		$this->setLeavesHeight($random->nextBoundedInt(5) + ($random->nextBoolean() ? 3 : 13));
	}

	protected function setLeavesHeight(int $leavesHeight) : void{
		$this->leavesHeight = $leavesHeight;
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		if($this->cannotGenerateAt($blockX, $blockY, $blockZ, $world)){
			return false;
		}

		// generates the leaves
		$previousRadius = 0;
		for($y = $blockY + $this->height - $this->leavesHeight; $y <= $blockY + $this->height; ++$y){
			$n = $blockY + $this->height - $y;
			$radius = (int) floor((float) $n / $this->leavesHeight * 3.5);
			if($radius === $previousRadius && $n > 0 && $y % 2 === 0){
				++$radius;
			}
			$this->generateLeaves($blockX, $y, $blockZ, $radius, false, $world);
			$previousRadius = $radius;
		}

		// generates the trunk
		$this->generateTrunk($world, $blockX, $blockY, $blockZ);

		// blocks below trunk are always dirt
		$this->generateDirtBelowTrunk($blockX, $blockY, $blockZ);
		return true;
	}

	protected function generateDirtBelowTrunk(int $blockX, int $blockY, int $blockZ) : void{
		// mega redwood tree does not replaces blocks below (surely to preserves podzol)
	}
}