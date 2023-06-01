<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class OreVein extends TerrainObject{

	/**
	 * The square of the percentage of the radius that is the distance between the given block's
	 * center and the center of an orthogonal ellipsoid. A block's center is inside the ellipsoid
	 * if and only if its normalizedSquaredCoordinate values add up to less than 1.
	 *
	 * @param float $origin the center of the spheroid
	 * @param float $radius the spheroid's radius on this axis
	 * @param int $x the raw coordinate
	 * @return float the square of the normalized coordinate
	 */
	protected static function normalizedSquaredCoordinate(float $origin, float $radius, int $x) : float{
		$squared_normalized_x = ($x + 0.5 - $origin) / $radius;
		$squared_normalized_x *= $squared_normalized_x;
		return $squared_normalized_x;
	}

	private Block $type;
	private int $amount;
	private int $target_type;

	/**
	 * Creates the instance for a given ore type.
	 *
	 * @param OreType $oreType the ore type
	 */
	public function __construct(OreType $oreType){
		$this->type = $oreType->type;
		$this->amount = $oreType->amount;
		$this->target_type = $oreType->target_type;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$angle = $random->nextFloat() * M_PI;
		$dx1 = $source_x + sin($angle) * $this->amount / 8.0;
		$dx2 = $source_x - sin($angle) * $this->amount / 8.0;
		$dz1 = $source_z + cos($angle) * $this->amount / 8.0;
		$dz2 = $source_z - cos($angle) * $this->amount / 8.0;
		$dy1 = $source_y + $random->nextBoundedInt(3) - 2;
		$dy2 = $source_y + $random->nextBoundedInt(3) - 2;
		$succeeded = false;
		for($i = 0; $i < $this->amount; ++$i){
			$origin_x = $dx1 + ($dx2 - $dx1) * $i / $this->amount;
			$origin_y = $dy1 + ($dy2 - $dy1) * $i / $this->amount;
			$origin_z = $dz1 + ($dz2 - $dz1) * $i / $this->amount;
			$q = $random->nextFloat() * $this->amount / 16.0;
			$radius_h = (sin($i * M_PI / $this->amount) + 1 * $q + 1) / 2.0;
			$radius_v = (sin($i * M_PI / $this->amount) + 1 * $q + 1) / 2.0;

			$min_x = (int) ($origin_x - $radius_h);
			$max_x = (int) ($origin_x + $radius_h);

			$min_y = (int) ($origin_y - $radius_v);
			$max_y = (int) ($origin_y + $radius_v);

			$min_z = (int) ($origin_z - $radius_h);
			$max_z = (int) ($origin_z + $radius_h);

			for($x = $min_x; $x <= $max_x; ++$x){
				// scale the center of x to the range [-1, 1] within the circle
				$squared_normalized_x = self::normalizedSquaredCoordinate($origin_x, $radius_h, $x);
				if($squared_normalized_x >= 1){
					continue;
				}
				for($y = $min_y; $y <= $max_y; ++$y){
					$squared_normalized_y = self::normalizedSquaredCoordinate($origin_y, $radius_v, $y);
					if($squared_normalized_x + $squared_normalized_y >= 1){
						continue;
					}
					for($z = $min_z; $z <= $max_z; ++$z){
						$squared_normalized_z = self::normalizedSquaredCoordinate($origin_z, $radius_h, $z);
						if($squared_normalized_x + $squared_normalized_y + $squared_normalized_z < 1 && $world->getBlockAt($x, $y, $z)->getTypeId() === $this->target_type){
							$world->setBlockAt($x, $y, $z, $this->type);
							$succeeded = true;
						}
					}
				}
			}
		}

		return $succeeded;
	}
}