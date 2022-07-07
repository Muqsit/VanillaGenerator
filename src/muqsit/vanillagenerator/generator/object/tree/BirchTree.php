<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;

class BirchTree extends GenericTree{

	/**
	 * Initializes this tree with a random height, preparing it to attempt to generate.
	 *
	 * @param Random $random the PRNG
	 * @param BlockTransaction $transaction the BlockTransaction used to check for space and to fill wood and leaf
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(3) + 5);
		$this->setType(VanillaBlocks::BIRCH_LOG(), VanillaBlocks::BIRCH_LEAVES());
	}
}