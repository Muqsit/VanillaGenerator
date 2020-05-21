<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\glowstone;

use muqsit\vanillagenerator\generator\noise\bukkit\BasePerlinNoiseGenerator;
use pocketmine\utils\Random;

class PerlinNoise extends BasePerlinNoiseGenerator{
	/** @noinspection MagicMethodsValidityInspection */
	/** @noinspection PhpMissingParentConstructorInspection */

	/**
	 * Creates an instance using the given PRNG.
	 * @param Random $rand the PRNG used to generate the seed permutation
	 */
	public function __construct(Random $rand){
		$this->offsetX = $rand->nextFloat() * 256;
		$this->offsetY = $rand->nextFloat() * 256;
		$this->offsetZ = $rand->nextFloat() * 256;

		// The only reason why I'm re-implementing the constructor code is that I've read
		// on at least 3 different sources that the permutation table should initially be
		// populated with indices.
		// "The permutation table is his answer to the issue of random numbers.
		// First take an array of decent length, usually 256 values. Fill it sequentially with each
		// number in that range: so index 1 gets 1, index 8 gets 8, index 251 gets 251, etc...
		// Then randomly shuffle the values so you have a table of 256 random values, but only
		// contains the values between 0 and 255."
		// source: https://code.google.com/p/fractalterraingeneration/wiki/Perlin_Noise
		for($i = 0; $i < 256; ++$i){
			$this->perm[$i] = $i;
		}

		for($i = 0; $i < 256; ++$i){
			$pos = $rand->nextBoundedInt(256 - $i) + $i;
			$old = $this->perm[$i];
			$this->perm[$i] = $this->perm[$pos];
			$this->perm[$pos] = $old;
			$this->perm[$i + 256] = $this->perm[$i];
		}
	}

	public static function floor(float $x) : int{
		$floored = (int) $x;
		return $x < $floored ? $floored - 1 : $floored;
	}

	/**
	 * Generates a rectangular section of this generator's noise.
	 *
	 * @param float[] $noise the output of the previous noise layer
	 * @param float $x the X offset
	 * @param float $y the Y offset
	 * @param float $z the Z offset
	 * @param int $sizeX the size on the X axis
	 * @param int $sizeY the size on the Y axis
	 * @param int $sizeZ the size on the Z axis
	 * @param float $scaleX the X scale parameter
	 * @param float $scaleY the Y scale parameter
	 * @param float $scaleZ the Z scale parameter
	 * @param float $amplitude the amplitude parameter
	 * @return float[] noise with this layer of noise added
	 */
	public function getNoise(array &$noise, float $x, float $y, float $z, int $sizeX, int $sizeY, int $sizeZ, float $scaleX, float $scaleY, float $scaleZ, float $amplitude) : array{
		if($sizeY === 1){
			return $this->get2dNoise($noise, $x, $z, $sizeX, $sizeZ, $scaleX, $scaleZ, $amplitude);
		}

		return $this->get3dNoise($noise, $x, $y, $z, $sizeX, $sizeY, $sizeZ, $scaleX, $scaleY, $scaleZ, $amplitude);
	}

	/**
	 * @param float[] $noise
	 * @param float $x
	 * @param float $z
	 * @param int $sizeX
	 * @param int $sizeZ
	 * @param float $scaleX
	 * @param float $scaleZ
	 * @param float $amplitude
	 * @return float[]
	 */
	protected function get2dNoise(array &$noise, float $x, float $z, int $sizeX, int $sizeZ, float $scaleX, float $scaleZ, float $amplitude) : array{
		$index = -1;
		for($i = 0; $i < $sizeX; ++$i){
			$dx = $x + $this->offsetX + $i * $scaleX;
			$floorX = self::floor($dx);
			$ix = $floorX & 255;
			$dx -= $floorX;
			$fx = self::fade($dx);
			for($j = 0; $j < $sizeZ; ++$j){
				$dz = $z + $this->offsetZ + $j * $scaleZ;
				$floorZ = self::floor($dz);
				$iz = $floorZ & 255;
				$dz -= $floorZ;
				$fz = self::fade($dz);
				// Hash coordinates of the square corners
				$a = $this->perm[$ix];
				$aa = $this->perm[$a] + $iz;
				$b = $this->perm[$ix + 1];
				$ba = $this->perm[$b] + $iz;
				$x1 = self::lerp($fx, self::grad($this->perm[$aa], $dx, 0, $dz), self::grad($this->perm[$ba], $dx - 1, 0, $dz));
				$x2 = self::lerp($fx, self::grad($this->perm[$aa + 1], $dx, 0, $dz - 1),
					self::grad($this->perm[$ba + 1], $dx - 1, 0, $dz - 1));

				$noise[++$index] += self::lerp($fz, $x1, $x2) * $amplitude;
			}
		}

		return $noise;
	}

	/**
	 * @param float[] $noise
	 * @param float $x
	 * @param float $y
	 * @param float $z
	 * @param int $sizeX
	 * @param int $sizeY
	 * @param int $sizeZ
	 * @param float $scaleX
	 * @param float $scaleY
	 * @param float $scaleZ
	 * @param float $amplitude
	 * @return float[]
	 */
	protected function get3dNoise(array &$noise, float $x, float $y, float $z, int $sizeX, int $sizeY, int $sizeZ, float $scaleX, float $scaleY, float $scaleZ, float $amplitude) : array{
		$n = -1;
		$x1 = 0;
		$x2 = 0;
		$x3 = 0;
		$x4 = 0;
		$index = -1;
		for($i = 0; $i < $sizeX; ++$i){
			$dx = $x + $this->offsetX + $i * $scaleX;
			$floorX = self::floor($dx);
			$ix = $floorX & 255;
			$dx -= $floorX;
			$fx = self::fade($dx);
			for($j = 0; $j < $sizeZ; ++$j){
				$dz = $z + $this->offsetZ + $j * $scaleZ;
				$floorZ = self::floor($dz);
				$iz = $floorZ & 255;
				$dz -= $floorZ;
				$fz = self::fade($dz);
				for($k = 0; $k < $sizeY; ++$k){
					$dy = $y + $this->offsetY + $k * $scaleY;
					$floorY = self::floor($dy);
					$iy = $floorY & 255;
					$dy -= $floorY;
					$fy = self::fade($dy);
					if($k === 0 || $iy !== $n){
						$n = $iy;
						// Hash coordinates of the cube corners
						$a = $this->perm[$ix] + $iy;
						$aa = $this->perm[$a] + $iz;
						$ab = $this->perm[$a + 1] + $iz;
						$b = $this->perm[$ix + 1] + $iy;
						$ba = $this->perm[$b] + $iz;
						$bb = $this->perm[$b + 1] + $iz;
						$x1 = self::lerp($fx, self::grad($this->perm[$aa], $dx, $dy, $dz), self::grad($this->perm[$ba], $dx - 1, $dy, $dz));
						$x2 = self::lerp($fx, self::grad($this->perm[$ab], $dx, $dy - 1, $dz),
							self::grad($this->perm[$bb], $dx - 1, $dy - 1, $dz));
						$x3 = self::lerp($fx, self::grad($this->perm[$aa + 1], $dx, $dy, $dz - 1),
							self::grad($this->perm[$ba + 1], $dx - 1, $y, $dz - 1));
						$x4 = self::lerp($fx, self::grad($this->perm[$ab + 1], $dx, $dy - 1, $dz - 1),
							self::grad($this->perm[$bb + 1], $dx - 1, $dy - 1, $dz - 1));
					}
					$y1 = self::lerp($fy, $x1, $x2);
					$y2 = self::lerp($fy, $x3, $x4);

					$noise[++$index] += self::lerp($fz, $y1, $y2) * $amplitude;
				}
			}
		}

		return $noise;
	}
}