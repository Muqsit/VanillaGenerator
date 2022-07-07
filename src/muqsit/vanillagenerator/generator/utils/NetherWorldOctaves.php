<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\utils;

use muqsit\vanillagenerator\generator\noise\bukkit\OctaveGenerator;

/**
 * @template T of OctaveGenerator
 * @template U of OctaveGenerator
 * @template V of OctaveGenerator
 * @template W of OctaveGenerator
 * @template X of OctaveGenerator
 * @template Y of OctaveGenerator
 *
 * @extends WorldOctaves<T, U, V, W>
 */
class NetherWorldOctaves extends WorldOctaves{

	/**
	 * @param T $height
	 * @param U $roughness
	 * @param U $roughness_2
	 * @param V $detail
	 * @param W $surface
	 * @param X $soul_sand
	 * @param Y $gravel
	 */
	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness_2,
		OctaveGenerator $detail,
		OctaveGenerator $surface,
		public OctaveGenerator $soul_sand,
		public OctaveGenerator $gravel
	){
		parent::__construct($height, $roughness, $roughness_2, $detail, $surface);
	}
}