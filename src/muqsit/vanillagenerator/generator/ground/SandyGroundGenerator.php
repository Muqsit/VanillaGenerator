<?php /** @noinspection MagicMethodsValidityInspection */

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use pocketmine\block\VanillaBlocks;

class SandyGroundGenerator extends GroundGenerator{

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->setTopMaterial(VanillaBlocks::SAND());
		$this->setGroundMaterial(VanillaBlocks::SAND());
	}
}