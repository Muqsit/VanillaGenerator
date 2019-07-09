<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class BiomeMapLayer extends MapLayer{

	/** @var int[] */
	private static $WARM = [BiomeIds::DESERT, BiomeIds::DESERT, BiomeIds::DESERT, BiomeIds::SAVANNA, BiomeIds::SAVANNA, BiomeIds::PLAINS];

	/** @var int[] */
	private static $WET = [BiomeIds::PLAINS, BiomeIds::PLAINS, BiomeIds::FOREST, BiomeIds::BIRCH_FOREST, BiomeIds::ROOFED_FOREST, BiomeIds::EXTREME_HILLS, BiomeIds::SWAMPLAND];

	/** @var int[] */
	private static $DRY = [BiomeIds::PLAINS, BiomeIds::FOREST, BiomeIds::TAIGA, BiomeIds::EXTREME_HILLS];

	/** @var int[] */
	private static $COLD = [BiomeIds::ICE_FLATS, BiomeIds::ICE_FLATS, BiomeIds::TAIGA_COLD];

	/** @var int[] */
	private static $WARM_LARGE = [BiomeIds::MESA_ROCK, BiomeIds::MESA_ROCK, BiomeIds::MESA_CLEAR_ROCK];

	/** @var int[] */
	private static $DRY_LARGE = [BiomeIds::REDWOOD_TAIGA];

	/** @var int[] */
	private static $WET_LARGE = [BiomeIds::JUNGLE];

	/** @var MapLayer */
	private $belowLayer;

	public function __construct(int $seed, MapLayer $belowLayer){
		parent::__construct($seed);
		$this->belowLayer = $belowLayer;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);

		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				$val = $values[$j + $i * $sizeX];
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

				$finalValues[$j + $i * $sizeX] = $val;
			}
		}

		return $finalValues;
	}
}