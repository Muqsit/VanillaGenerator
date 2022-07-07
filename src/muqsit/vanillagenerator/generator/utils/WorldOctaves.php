<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\utils;

use muqsit\vanillagenerator\generator\noise\bukkit\OctaveGenerator;

/**
 * @template T of OctaveGenerator
 * @template U of OctaveGenerator
 * @template V of OctaveGenerator
 * @template W of OctaveGenerator
 */
class WorldOctaves{

	/** @var T */
	public OctaveGenerator $height;

	/** @var U */
	public OctaveGenerator $roughness;

	/** @var U */
	public OctaveGenerator $roughness_2;

	/** @var V */
	public OctaveGenerator $detail;

	/** @var W */
	public OctaveGenerator $surface;

	/**
	 * @param T $height
	 * @param U $roughness
	 * @param U $roughness_2
	 * @param V $detail
	 * @param W $surface
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