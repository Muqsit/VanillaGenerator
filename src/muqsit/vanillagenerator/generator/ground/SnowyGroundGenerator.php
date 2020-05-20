<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use pocketmine\block\VanillaBlocks;

class SnowyGroundGenerator extends GroundGenerator{

	public function __construct(){
		parent::__construct(VanillaBlocks::SNOW_LAYER());
	}
}