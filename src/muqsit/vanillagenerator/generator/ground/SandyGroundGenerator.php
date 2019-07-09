<?php /** @noinspection MagicMethodsValidityInspection */

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

class SandyGroundGenerator extends GroundGenerator{

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->setTopMaterial(self::$SAND);
		$this->setGroundMaterial(self::$SAND);
	}
}