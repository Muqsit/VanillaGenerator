<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

class BiomeHeight{

	/** @var float */
	private $height;

	/** @var float */
	private $scale;

	public function __construct(float $height, float $scale){
		$this->height = $height;
		$this->scale = $scale;
	}

	public function getHeight() : float{
		return $this->height;
	}

	public function getScale() : float{
		return $this->scale;
	}
}