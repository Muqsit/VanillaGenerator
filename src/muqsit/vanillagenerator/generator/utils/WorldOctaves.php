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
	public $height;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var U
	 */
	public $roughness;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var U
	 */
	public $roughness2;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var V
	 */
	public $detail;

	/**
	 * @var OctaveGenerator
	 *
	 * @phpstan-var W
	 */
	public $surface;

	/**
	 * @param OctaveGenerator $height
	 * @param OctaveGenerator $roughness
	 * @param OctaveGenerator $roughness2
	 * @param OctaveGenerator $detail
	 * @param OctaveGenerator $surface
	 *
	 * @phpstan-param T $height
	 * @phpstan-param U $roughness
	 * @phpstan-param U $roughness2
	 * @phpstan-param V $detail
	 * @phpstan-param W $surface
	 */
	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness2,
		OctaveGenerator $detail,
		OctaveGenerator $surface
	){
		$this->height = $height;
		$this->roughness = $roughness;
		$this->roughness2 = $roughness2;
		$this->detail = $detail;
		$this->surface = $surface;
	}
}