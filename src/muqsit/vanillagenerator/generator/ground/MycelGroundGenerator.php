<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use pocketmine\block\VanillaBlocks;

class MycelGroundGenerator extends GroundGenerator{

	/** @noinspection MagicMethodsValidityInspection */
	public function __construct(){
		$this->setTopMaterial(VanillaBlocks::MYCELIUM());
	}
}