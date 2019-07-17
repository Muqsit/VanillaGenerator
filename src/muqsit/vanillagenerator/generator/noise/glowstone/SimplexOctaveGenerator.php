<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\glowstone;

use pocketmine\utils\Random;

class SimplexOctaveGenerator extends PerlinOctaveGenerator{

	protected static function createOctaves(Random $rand, int $octaves) : array{
		$result = [];

		for($i = 0; $i < $octaves; ++$i){
			$result[$i] = new SimplexNoise($rand);
		}

		return $result;
	}

	/**
	 * @param Random $random
	 * @param int $octaves
	 * @param int $sizeX
	 * @param int $sizeY
	 * @param int $sizeZ
	 * @return SimplexOctaveGenerator
	 */
	public static function fromRandomAndOctaves(Random $random, int $octaves, int $sizeX, int $sizeY, int $sizeZ){
		return new SimplexOctaveGenerator(self::createOctaves($random, $octaves), $random, $sizeX, $sizeY, $sizeZ);
	}

	public function getFractalBrownianMotion(float $x, float $y, float $z, float $lacunarity, float $persistence) : array{
		foreach($this->noise as $k => $_){
			$this->noise[$k] = 0.0;
		}

		$freq = 1.0;
		$amp = 1.0;

		// fBm
		foreach($this->octaves as $octave){
			$this->noise = $octave->getNoise($this->noise, $x, $y, $z, $this->sizeX, $this->sizeY, $this->sizeZ, $this->xScale * $freq, $this->yScale * $freq, $this->zScale * $freq, 0.55 / $amp);
			$freq *= $lacunarity;
			$amp *= $persistence;
		}

		return $this->noise;
	}
}