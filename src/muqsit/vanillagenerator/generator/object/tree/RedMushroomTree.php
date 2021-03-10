<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockLegacyIds;

class RedMushroomTree extends BrownMushroomTree{

	protected int $type = BlockLegacyIds::RED_MUSHROOM_BLOCK;
}