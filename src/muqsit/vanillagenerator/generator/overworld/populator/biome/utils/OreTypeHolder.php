<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome\utils;

use muqsit\vanillagenerator\generator\object\OreType;

final class OreTypeHolder{

	/** @var OreType */
	public OreType $type;

	/** @var int */
	public int $value;

	public function __construct(OreType $type, int $value){
		$this->type = $type;
		$this->value = $value;
	}
}