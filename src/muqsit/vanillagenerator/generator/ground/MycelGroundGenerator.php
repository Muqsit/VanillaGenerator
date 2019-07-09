<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

class MycelGroundGenerator extends GroundGenerator{

	public function __construct(){
		$this->setTopMaterial(self::$MYCEL);
	}
}