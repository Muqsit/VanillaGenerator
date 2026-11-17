<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;

class OreType{

	readonly public Block $target_type;
	readonly public int $amount;

	/**
	 * Creates an ore type. If {@code min_y} and {@code max_y} are equal, then the height range is
	 * 0 to {@code min_y}*2, with greatest density around {@code min_y}. Otherwise, density is uniform
	 * over the height range.
	 *
	 * @param Block $type the block type
	 * @param int $min_y the minimum height
	 * @param int $max_y the maximum height
	 * @param int $amount the size of a vein
	 * @param Block|null $target_type the block this can replace
	 */
	public function __construct(
		readonly public Block $type,
		readonly public int $min_y,
		readonly public int $max_y,
		int $amount,
		?Block $target_type = null
	){
		$this->amount = $amount + 1;
		$this->target_type = $target_type ?? VanillaBlocks::STONE();
	}

	/**
	 * Generates a random height at which a vein of this ore can spawn.
	 *
	 * @param Random $random the PRNG to use
	 * @return int a random height for this ore
	 */
	public function getRandomHeight(Random $random) : int{
		return $this->min_y === $this->max_y
			? $random->nextBoundedInt($this->min_y) + $random->nextBoundedInt($this->min_y)
			: $random->nextBoundedInt($this->max_y - $this->min_y) + $this->min_y;
	}
}