<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome\utils;

use muqsit\vanillagenerator\generator\object\OreType;

final class OreTypeHolder{

	public function __construct(
		public OreType $type,
		public int $value
	){}
}