<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class BiomeMapLayer extends MapLayer{

	/** @var int[] */
	private static array $WARM = [BiomeIds::DESERT, BiomeIds::DESERT, BiomeIds::DESERT, BiomeIds::SAVANNA, BiomeIds::SAVANNA, BiomeIds::PLAINS];

	/** @var int[] */
	private static array $WET = [BiomeIds::PLAINS, BiomeIds::PLAINS, BiomeIds::FOREST, BiomeIds::BIRCH_FOREST, BiomeIds::ROOFED_FOREST, BiomeIds::EXTREME_HILLS, BiomeIds::SWAMPLAND];

	/** @var int[] */
	private static array $DRY = [BiomeIds::PLAINS, BiomeIds::FOREST, BiomeIds::TAIGA, BiomeIds::EXTREME_HILLS];

	/** @var int[] */
	private static array $COLD = [BiomeIds::ICE_PLAINS, BiomeIds::ICE_PLAINS, BiomeIds::COLD_TAIGA];

	/** @var int[] */
	private static array $WARM_LARGE = [BiomeIds::MESA_PLATEAU_STONE, BiomeIds::MESA_PLATEAU_STONE, BiomeIds::MESA_PLATEAU];

	/** @var int[] */
	private static array $DRY_LARGE = [BiomeIds::MEGA_TAIGA];

	/** @var int[] */
	private static array $WET_LARGE = [BiomeIds::JUNGLE];

	private MapLayer $below_layer;

	public function __construct(int $seed, MapLayer $below_layer){
		parent::__construct($seed);
		$this->below_layer = $below_layer;
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$values = $this->below_layer->generateValues($x, $z, $size_x, $size_z);

		$final_values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				$val = $values[$j + $i * $size_x];
				if($val !== 0){
					$this->setCoordsSeed($x + $j, $z + $i);
					switch($val){
						case 1:
							$val = self::$DRY[$this->nextInt(count(self::$DRY))];
							break;
						case 2:
							$val = self::$WARM[$this->nextInt(count(self::$WARM))];
							break;
						case 3:
						case 1003:
							$val = self::$COLD[$this->nextInt(count(self::$COLD))];
							break;
						case 4:
							$val = self::$WET[$this->nextInt(count(self::$WET))];
							break;
						case 1001:
							$val = self::$DRY_LARGE[$this->nextInt(count(self::$DRY_LARGE))];
							break;
						case 1002:
							$val = self::$WARM_LARGE[$this->nextInt(count(self::$WARM_LARGE))];
							break;
						case 1004:
							$val = self::$WET_LARGE[$this->nextInt(count(self::$WET_LARGE))];
							break;
						default:
							break;
					}
				}

				$final_values[$j + $i * $size_x] = $val;
			}
		}

		return $final_values;
	}
}