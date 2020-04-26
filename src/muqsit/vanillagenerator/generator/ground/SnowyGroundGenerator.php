<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

class SnowyGroundGenerator extends GroundGenerator{

	/** @noinspection MagicMethodsValidityInspection */
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->setGroundMaterial(self::$AIR);//fixes fatal error
		$this->setTopMaterial(self::$SNOW);
	}
}
