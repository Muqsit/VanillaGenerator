<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\utils;

use muqsit\vanillagenerator\generator\noise\bukkit\OctaveGenerator;

/**
 * @phpstan-template T of OctaveGenerator
 * @phpstan-template U of OctaveGenerator
 * @phpstan-template V of OctaveGenerator
 * @phpstan-template W of OctaveGenerator
 */
class WorldOctaves{

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var T
	 */
	public OctaveGenerator $height;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var U
	 */
	public OctaveGenerator $roughness;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var U
	 */
	public OctaveGenerator $roughness_2;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var V
	 */
	public OctaveGenerator $detail;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var W
	 */
	public OctaveGenerator $surface;

	/**
	 * @param OctaveGenerator $height
	 * @param OctaveGenerator $roughness
	 * @param OctaveGenerator $roughness_2
	 * @param OctaveGenerator $detail
	 * @param OctaveGenerator $surface
	 *
	 * @phpstan-param T $height
	 * @phpstan-param U $roughness
	 * @phpstan-param U $roughness_2
	 * @phpstan-param V $detail
	 * @phpstan-param W $surface
	 */
	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness_2,
		OctaveGenerator $detail,
		OctaveGenerator $surface
	){
		$this->height = $height;
		$this->roughness = $roughness;
		$this->roughness_2 = $roughness_2;
		$this->detail = $detail;
		$this->surface = $surface;
	}
}