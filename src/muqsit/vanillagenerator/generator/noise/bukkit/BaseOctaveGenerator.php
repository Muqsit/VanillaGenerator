<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\bukkit;

abstract class BaseOctaveGenerator{

	/** @var NoiseGenerator[] */
	protected array $octaves;

	/** @var float */
	protected float $x_scale = 1.0;

	/** @var float */
	protected float $y_scale = 1.0;

	/** @var float */
	protected float $z_scale = 1.0;

	/**
	 * @param NoiseGenerator[] $octaves
	 */
	protected function __construct(array $octaves){
		$this->octaves = $octaves;
	}

	/**
	 * Sets the scale used for all coordinates passed to this generator.
	 * <p>
	 * This is the equivalent to setting each coordinate to the specified
	 * value.
	 *
	 * @param float $scale New value to scale each coordinate by
	 */
	public function setScale(float $scale) : void{
		$this->setXScale($scale);
		$this->setYScale($scale);
		$this->setZScale($scale);
	}

	/**
	 * Gets the scale used for each X-coordinates passed
	 *
	 * @return float X scale
	 */
	public function getXScale() : float{
		return $this->x_scale;
	}

	/**
	 * Sets the scale used for each X-coordinates passed
	 *
	 * @param float $scale New X scale
	 */
	public function setXScale(float $scale) : void{
		$this->x_scale = $scale;
	}

	/**
	 * Gets the scale used for each Y-coordinates passed
	 *
	 * @return float Y scale
	 */
	public function getYScale() : float{
		return $this->y_scale;
	}

	/**
	 * Sets the scale used for each Y-coordinates passed
	 *
	 * @param float $scale New Y scale
	 */
	public function setYScale(float $scale) : void{
		$this->y_scale = $scale;
	}

	/**
	 * Gets the scale used for each Z-coordinates passed
	 *
	 * @return float Z scale
	 */
	public function getZScale() : float{
		return $this->z_scale;
	}

	/**
	 * Sets the scale used for each Z-coordinates passed
	 *
	 * @param float $scale New Z scale
	 */
	public function setZScale(float $scale) : void{
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