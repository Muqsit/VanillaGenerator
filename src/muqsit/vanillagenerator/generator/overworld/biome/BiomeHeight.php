<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

class BiomeHeight{

	public function __construct(
		private float $height,
		private float $scale
	){}

	public function getHeight() : float{
		return $this->height;
	}

	public function getScale() : float{
		return $this->scale;
	}
}