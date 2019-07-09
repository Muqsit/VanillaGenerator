<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

class RockyGroundGenerator extends GroundGenerator{

	/** @noinspection MagicMethodsValidityInspection */
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->setTopMaterial(self::$STONE);
		$this->setGroundMaterial(self::$STONE);
	}
}