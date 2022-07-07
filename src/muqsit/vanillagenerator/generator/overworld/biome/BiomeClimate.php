<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

class BiomeClimate{

	public function __construct(
		private float $temperature,
		private float $humidity,
		private bool $rainy
	){}

	public function getTemperature() : float{
		return $this->temperature;
	}

	public function getHumidity() : float{
		return $this->humidity;
	}

	public function isRainy() : bool{
		return $this->rainy;
	}
}