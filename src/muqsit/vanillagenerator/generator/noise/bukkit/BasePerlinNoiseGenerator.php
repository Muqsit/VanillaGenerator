<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\noise\bukkit;

use pocketmine\utils\Random;

abstract class BasePerlinNoiseGenerator extends NoiseGenerator{

	/** @var int[][] */
	protected const GRAD3 = [
		[1, 1, 0], [-1, 1, 0], [1, -1, 0], [-1, -1, 0],
		[1, 0, 1], [-1, 0, 1], [1, 0, -1], [-1, 0, -1],
		[0, 1, 1], [0, -1, 1], [0, 1, -1], [0, -1, -1]
	];

	public function __construct(?Random $rand = null){
		if($rand === null){
			static $p = [
				151, 160, 137, 91, 90, 15, 131, 13, 201,
				95, 96, 53, 194, 233, 7, 225, 140, 36, 103, 30, 69, 142, 8, 99, 37,
				240, 21, 10, 23, 190, 6, 148, 247, 120, 234, 75, 0, 26, 197, 62,
				94, 252, 219, 203, 117, 35, 11, 32, 57, 177, 33, 88, 237, 149, 56,
				87, 174, 20, 125, 136, 171, 168, 68, 175, 74, 165, 71, 134, 139,
				48, 27, 166, 77, 146, 158, 231, 83, 111, 229, 122, 60, 211, 133,
				230, 220, 105, 92, 41, 55, 46, 245, 40, 244, 102, 143, 54, 65, 25,
				63, 161, 1, 216, 80, 73, 209, 76, 132, 187, 208, 89, 18, 169, 200,
				196, 135, 130, 116, 188, 159, 86, 164, 100, 109, 198, 173, 186, 3,
				64, 52, 217, 226, 250, 124, 123, 5, 202, 38, 147, 118, 126, 255,
				82, 85, 212, 207, 206, 59, 227, 47, 16, 58, 17, 182, 189, 28, 42,
				223, 183, 170, 213, 119, 248, 152, 2, 44, 154, 163, 70, 221, 153,
				101, 155, 167, 43, 172, 9, 129, 22, 39, 253, 19, 98, 108, 110, 79,
				113, 224, 232, 178, 185, 112, 104, 218, 246, 97, 228, 251, 34, 242,
				193, 238, 210, 144, 12, 191, 179, 162, 241, 81, 51, 145, 235, 249,
				14, 239, 107, 49, 192, 214, 31, 181, 199, 106, 157, 184, 84, 204,
				176, 115, 121, 50, 45, 127, 4, 150, 254, 138, 236, 205, 93, 222,
				114, 67, 29, 24, 72, 243, 141, 128, 195, 78, 66, 215, 61, 156, 180
			];

			for($i = 0; $i < 512; ++$i){
				$this->perm[$i] = $p[$i & 255];
			}
		}else{
			$this->offset_x = $rand->nextFloat() * 256;
			$this->offset_y = $rand->nextFloat() * 256;
			$this->offset_z = $rand->nextFloat() * 256;

			for($i = 0; $i < 256; ++$i){
				$this->perm[$i] = $rand->nextBoundedInt(256);
			}

			for($i = 0; $i < 256; ++$i){
				$pos = $rand->nextBoundedInt(256 - $i) + $i;
				$old = $this->perm[$i];

				$this->perm[$i] = $this->perm[$pos];
				$this->perm[$pos] = $old;
				$this->perm[$i + 256] = $this->perm[$i];
			}
		}
	}

	public function noise3d(float $x, float $y = 0.0, float $z = 0.0) : float{
		$x += $this->offset_x;
		$y += $this->offset_y;
		$z += $this->offset_z;

		$floor_x = self::floor($x);
		$floor_y = self::floor($y);
		$floor_z = self::floor($z);

		// Find unit cube containing the point
		$X = $floor_x & 255;
		$Y = $floor_y & 255;
		$Z = $floor_z & 255;

		// Get relative xyz coordinates of the point within the cube
		$x -= $floor_x;
		$y -= $floor_y;
		$z -= $floor_z;

		// Compute fade curves for xyz
		$fX = self::fade($x);
		$fY = self::fade($y);
		$fZ = self::fade($z);

		// Hash coordinates of the cube corners
		$A = $this->perm[$X] + $Y;
		$AA = $this->perm[$A] + $Z;
		$AB = $this->perm[$A + 1] + $Z;
		$B = $this->perm[$X + 1] + $Y;
		$BA = $this->perm[$B] + $Z;
		$BB = $this->perm[$B + 1] + $Z;

		return self::lerp($fZ, self::lerp($fY, self::lerp($fX, self::grad($this->perm[$AA], $x, $y, $z),
			self::grad($this->perm[$BA], $x - 1, $y, $z)),
			self::lerp($fX, self::grad($this->perm[$AB], $x, $y - 1, $z),
				self::grad($this->perm[$BB], $x - 1, $y - 1, $z))),
			self::lerp($fY, self::lerp($fX, self::grad($this->perm[$AA + 1], $x, $y, $z - 1),
				self::grad($this->perm[$BA + 1], $x - 1, $y, $z - 1)),
				self::lerp($fX, self::grad($this->perm[$AB + 1], $x, $y - 1, $z - 1),
					self::grad($this->perm[$BB + 1], $x - 1, $y - 1, $z - 1))));
	}
}