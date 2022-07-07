<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;

class JungleTree extends GenericTree{

	/**
	 * Initializes this tree with a random height, preparing it to attempt to generate.
	 * @param Random $random
	 * @param BlockTransaction $transaction
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(7) + 4);
		$this->setType(VanillaBlocks::JUNGLE_LOG(), VanillaBlocks::JUNGLE_LEAVES());
	}
}