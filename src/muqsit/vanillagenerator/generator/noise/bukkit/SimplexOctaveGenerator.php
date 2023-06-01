<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\bukkit;

use pocketmine\utils\Random;

class SimplexOctaveGenerator extends BaseOctaveGenerator{

	/**
	 * @param Random $rand
	 * @param int $octaves
	 * @return SimplexNoiseGenerator[]
	 */
	private static function createOctaves(Random $rand, int $octaves) : array{
		$result = [];

		for($i = 0; $i < $octaves; ++$i){
			$result[$i] = new SimplexNoiseGenerator($rand);
		}

		return $result;
	}

	public float $w_scale = 1.0;

	/**
	 * Creates a simplex octave generator for the given {@link Random}
	 *
	 * @param Random $rand
	 * @param int $octaves Amount of octaves to create
	 */
	public function __construct(Random $rand, int $octaves){
		parent::__construct(self::createOctaves($rand, $octaves));
	}

	public function setScale(float $scale) : void{
		parent::setScale($scale);
		$this->w_scale = $scale;
	}

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
	public function octaveNoise(float $x, float $y, float $z, float $frequency, float $amplitude, bool $normalized) : float{
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

	/**
	 * Generates noise for the 3D coordinates using the specified number of
	 * octaves and parameters
	 *
	 * @param float $x X-coordinate
	 * @param float $y Y-coordinate
	 * @param float $z Z-coordinate
	 * @param float $w W-coordinate
	 * @param float $frequency How much to alter the frequency by each octave
	 * @param float $amplitude How much to alter the amplitude by each octave
	 * @param bool $normalized If true, normalize the value to [-1, 1]
	 * @return float resulting noise
	 */
	public function noise(float $x, float $y, float $z, float $w, float $frequency, float $amplitude, bool $normalized = false) : float{
		$result = 0.0;
		$amp = 1.0;
		$freq = 1.0;
		$max = 0.0;

		$x *= $this->x_scale;
		$y *= $this->y_scale;
		$z *= $this->z_scale;
		$w *= $this->w_scale;

		/** @var SimplexNoiseGenerator $octave */
		foreach($this->octaves as $octave){
			$result += $octave->noise($x * $freq, $y * $freq, $z * $freq, $w * $freq) * $amp;
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