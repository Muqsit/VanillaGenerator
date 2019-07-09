<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\utils\Random;

class OreType{

	/** @var Block */
	private $type;

	/** @var int */
	private $minY;

	/** @var int */
	private $maxY;

	/** @var int */
	private $amount;

	/** @var int */
	private $targetType;

	/**
	 * Creates an ore type. If {@code minY} and {@code maxY} are equal, then the height range is
	 * 0 to {@code minY}*2, with greatest density around {@code minY}. Otherwise, density is uniform
	 * over the height range.
	 *
	 * @param Block $type the block type
	 * @param int $minY the minimum height
	 * @param int $maxY the maximum height
	 * @param int $amount the size of a vein
	 * @param int $targetType the block this can replace
	 */
	public function __construct(Block $type, int $minY, int $maxY, int $amount, int $targetType = BlockLegacyIds::STONE){
		$this->type = $type;
		$this->minY = $minY;
		$this->maxY = $maxY;
		$this->amount = ++$amount;
		$this->targetType = $targetType;
	}

	public function getType() : Block{
		return $this->type;
	}

	public function getMinY() : int{
		return $this->minY;
	}

	public function getMaxY() : int{
		return $this->maxY;
	}

	public function getAmount() : int{
		return $this->amount;
	}

	public function getTargetType() : int{
		return $this->targetType;
	}

	/**
	 * Generates a random height at which a vein of this ore can spawn.
	 *
	 * @param Random $random the PRNG to use
	 * @return int a random height for this ore
	 */
	public function getRandomHeight(Random $random) : int{
		return $this->minY === $this->maxY
			? $random->nextBoundedInt($this->minY) + $random->nextBoundedInt($this->minY)
			: $random->nextBoundedInt($this->maxY - $this->minY) + $this->minY;
	}
}