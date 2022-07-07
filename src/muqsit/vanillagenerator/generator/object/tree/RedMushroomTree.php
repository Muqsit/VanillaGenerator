<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\RedMushroomBlock;
use pocketmine\block\VanillaBlocks;

class RedMushroomTree extends BrownMushroomTree{

	protected function getType() : RedMushroomBlock{
		return VanillaBlocks::RED_MUSHROOM_BLOCK();
	}
}