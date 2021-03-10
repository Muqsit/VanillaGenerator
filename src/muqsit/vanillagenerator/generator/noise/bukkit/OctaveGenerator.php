<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\bukkit;

abstract class OctaveGenerator extends BaseOctaveGenerator{

	/**
	 * Generates noise for the 3D coordinates using the specified number of
	 * octaves and parameters
	 *
	 * @param float $x X-coordinate
	 * @param float $y Y-coordinate
	 * @param float $z Z-coordinate
	 * @param float $frequency How much to alter the frequency by each octave
	 * @param float $amplitude How much to alter the amplitude by each octave
	 * @param bool $normalized If true, normalize the value to [-1, 1]
	 * @return float resulting noise
	 */
	public function noise(float $x, float $y, float $z, float $frequency, float $amplitude, bool $normalized) : float{
		$result = 0.0;
		$amp = 1.0;
		$freq = 1.0;
		$max = 0.0;

		$x *= $this->x_scale;
		$y *= $this->y_scale;
		$z *= $this->z_scale;

		foreach($this->octaves as $octave){
			$result += $octave->noise3d($x * $freq, $y * $freq, $z * $freq) * $amp;
			$max += $amp;
			$freq *= $frequency;
			$amp *= $amplitude;
		}

		if($normalized){
			$result /= $max;
		}

		return $result;
	}
}