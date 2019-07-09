<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

class BiomeClimate{

	/** @var float */
	private $temperature;

	/** @var float */
	private $humidity;

	/** @var bool */
	private $rainy;

	public function __construct(float $temperature, float $humidity, bool $rainy){
		$this->temperature = $temperature;
		$this->humidity = $humidity;
		$this->rainy = $rainy;
	}

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