<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\glowstone;

use muqsit\vanillagenerator\generator\noise\bukkit\NoiseGenerator;
use muqsit\vanillagenerator\generator\noise\bukkit\OctaveGenerator;
use pocketmine\utils\Random;

class PerlinOctaveGenerator extends OctaveGenerator{

	/**
	 * @param Random $rand
	 * @param int $octaves
	 * @return PerlinNoise[]
	 */
	protected static function createOctaves(Random $rand, int $octaves) : array{
		$result = [];

		for($i = 0; $i < $octaves; ++$i){
			$result[$i] = new PerlinNoise($rand);
		}

		return $result;
	}

	protected static function floor(float $x) : int{
		return $x >= 0 ? (int) $x : (int) $x - 1;
	}

	/**
	 * @param Random $random
	 * @param int $octaves
	 * @param int $sizeX
	 * @param int $sizeY
	 * @param int $sizeZ
	 * @return PerlinOctaveGenerator
	 */
	public static function fromRandomAndOctaves(Random $random, int $octaves, int $sizeX, int $sizeY, int $sizeZ){
		return new PerlinOctaveGenerator(self::createOctaves($random, $octaves), $sizeX, $sizeY, $sizeZ);
	}

	/** @var int */
	protected $sizeX;

	/** @var int */
	protected $sizeY;

	/** @var int */
	protected $sizeZ;

	/** @var float[] */
	protected $noise;

	/**
	 * Creates a generator for multiple layers of Perlin noise.
	 *
	 * @param NoiseGenerator[] $octaves the noise generators
	 * @param int $sizeX the size on the X axis
	 * @param int $sizeY the size on the Y axis
	 * @param int $sizeZ the size on the Z axis
	 */
	public function __construct(array $octaves, int $sizeX, int $sizeY, int $sizeZ){
		parent::__construct($octaves);
		$this->sizeX = $sizeX;
		$this->sizeY = $sizeY;
		$this->sizeZ = $sizeZ;
		$this->noise = array_fill(0, $sizeX * $sizeY * $sizeZ, 0.0);
	}

	public function getSizeX() : int{
		return $this->sizeX;
	}

	public function getSizeY() : int{
		return $this->sizeY;
	}

	public function getSizeZ() : int{
		return $this->sizeZ;
	}

	public function setSizeX(int $sizeX) : void{
		$this->sizeX = $sizeX;
	}

	public function setSizeY(int $sizeY) : void{
		$this->sizeY = $sizeY;
	}

	public function setSizeZ(int $sizeZ) : void{
		$this->sizeZ = $sizeZ;
	}

	/**
	 * Generates multiple layers of noise.
	 *
	 * @param float $x the starting X coordinate
	 * @param float $y the starting Y coordinate
	 * @param float $z the starting Z coordinate
	 * @param float $lacunarity layer n's frequency as a fraction of layer {@code n - 1}'s frequency
	 * @param float $persistence layer n's amplitude as a multiple of layer {@code n - 1}'s amplitude
	 * @return float[] the noise array
	 */
	public function getFractalBrownianMotion(float $x, float $y, float $z, float $lacunarity, float $persistence) : array{
		$this->noise = array_fill(0, $this->sizeX * $this->sizeY * $this->sizeZ, 0.0);

		$freq = 1;
		$amp = 1;

		$x *= $this->xScale;
		$y *= $this->yScale;
		$z *= $this->zScale;

		// fBm
		// the noise have to be periodic over x and z axis: otherwise it can go crazy with high
		// input, leading to strange oddities in terrain generation like the old minecraft farland
		// symptoms.
		/** @var PerlinNoise $octave */
		foreach($this->octaves as $octave){
			$dx = $x * $freq;
			$dz = $z * $freq;
			// compute integer part
			$lx = self::floor($dx);
			$lz = self::floor($dz);
			// compute fractional part
			$dx -= $lx;
			$dz -= $lz;
			// wrap integer part to 0..16777216
			$lx %= 16777216;
			$lz %= 16777216;
			// add to fractional part
			$dx += $lx;
			$dz += $lz;

			$dy = $y * $freq;
			$this->noise = $octave->getNoise($this->noise, $dx, $dy, $dz, $this->sizeX, $this->sizeY, $this->sizeZ, $this->xScale * $freq, $this->yScale * $freq, $this->zScale * $freq, $amp);
			$freq *= $lacunarity;
			$amp *= $persistence;
		}

		return $this->noise;
	}
}