<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\bukkit;

abstract class NoiseGenerator{

	/** @var int[] */
	protected array $perm = [];

	protected float $offset_x;
	protected float $offset_y;
	protected float $offset_z;

	public static function floor(float $x) : int{
		return $x >= 0 ? (int) $x : (int) $x - 1;
	}

	protected static function fade(float $x) : float{
		return $x * $x * $x * ($x * ($x * 6 - 15) + 10);
	}

	protected static function lerp(float $x, float $y, float $z) : float{
		return $y + $x * ($z - $y);
	}

	protected static function grad(int $hash, float $x, float $y, float $z) : float{
		$hash &= 15;
		$u = $hash < 8 ? $x : $y;
		$v = $hash < 4 ? $y : ($hash === 12 || $hash === 14 ? $x : $z);
		return (($hash & 1) === 0 ? $u : -$u) + (($hash & 2) === 0 ? $v : -$v);
	}

	/**
	 * Computes and returns the 3D noise for the given coordinates in 3D space
	 *
	 * @param float $x X coordinate
	 * @param float $y Y coordinate
	 * @param float $z Z coordinate
	 * @return float at given location, from range -1 to 1
	 */
	abstract public function noise3d(float $x, float $y = 0.0, float $z = 0.0) : float;
}