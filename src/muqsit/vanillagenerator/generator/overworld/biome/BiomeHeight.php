<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

class BiomeHeight{

	public function __construct(
		readonly public float $height,
		readonly public float $scale
	){}
}