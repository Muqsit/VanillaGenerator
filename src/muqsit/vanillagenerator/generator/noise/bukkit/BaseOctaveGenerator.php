<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\bukkit;

abstract class BaseOctaveGenerator{

	public float $x_scale = 1.0;
	public float $y_scale = 1.0;
	public float $z_scale = 1.0;

	/**
	 * @param NoiseGenerator[] $octaves
	 */
	protected function __construct(
		protected array $octaves
	){}

	/**
	 * Sets the scale used for all coordinates passed to this generator.
	 * <p>
	 * This is the equivalent to setting each coordinate to the specified
	 * value.
	 *
	 * @param float $scale New value to scale each coordinate by
	 */
	public function setScale(float $scale) : void{
		$this->x_scale = $scale;
		$this->y_scale = $scale;
		$this->z_scale = $scale;
	}

	/**
	 * Gets a clone of the individual octaves used within this generator
	 *
	 * @return NoiseGenerator[] clone of the individual octaves
	 */
	public function getOctaves() : array{
		$octaves = [];
		foreach($this->octaves as $key => $value){
			$octaves[$key] = clone $value;
		}

		return $octaves;
	}
}