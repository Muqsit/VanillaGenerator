<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use ReflectionClass;

class BiomeVariationMapLayer extends MapLayer{

	/** @var int[] */
	private static $ISLANDS = [BiomeIds::PLAINS, BiomeIds::FOREST];

	/** @var int[][] */
	private static $VARIATIONS = [
		BiomeIds::DESERT => [BiomeIds::DESERT_HILLS],
		BiomeIds::FOREST => [BiomeIds::FOREST_HILLS],
		BiomeIds::BIRCH_FOREST => [BiomeIds::BIRCH_FOREST_HILLS],
		BiomeIds::ROOFED_FOREST => [BiomeIds::PLAINS],
		BiomeIds::TAIGA => [BiomeIds::TAIGA_HILLS],
		BiomeIds::REDWOOD_TAIGA => [BiomeIds::REDWOOD_TAIGA_HILLS],
		BiomeIds::TAIGA_COLD => [BiomeIds::TAIGA_COLD_HILLS],
		BiomeIds::PLAINS => [BiomeIds::FOREST, BiomeIds::FOREST, BiomeIds::FOREST_HILLS],
		BiomeIds::ICE_FLATS => [BiomeIds::ICE_MOUNTAINS],
		BiomeIds::JUNGLE => [BiomeIds::JUNGLE_HILLS],
		BiomeIds::OCEAN => [BiomeIds::DEEP_OCEAN],
		BiomeIds::EXTREME_HILLS => [BiomeIds::EXTREME_HILLS_WITH_TREES],
		BiomeIds::SAVANNA => [BiomeIds::SAVANNA_ROCK],
		BiomeIds::MESA_ROCK => [BiomeIds::MESA],
		BiomeIds::MESA_CLEAR_ROCK => [BiomeIds::MESA],
		BiomeIds::MESA => [BiomeIds::MESA]
	];

	/** @var string[] */
	private static $BIOMES;

	public static function init() : void{
		self::$BIOMES = [];
		foreach((new ReflectionClass(BiomeIds::class))->getConstants() as $const => $biomeId){
			self::$BIOMES[$biomeId] = $const;
		}
	}

	/** @var MapLayer */
	private $belowLayer;

	/** @var MapLayer|null */
	private $variationLayer;

	public function __construct(int $seed, MapLayer $belowLayer, ?MapLayer $variationLayer = null){
		parent::__construct($seed);
		$this->belowLayer = $belowLayer;
		$this->variationLayer = $variationLayer;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		if($this->variationLayer === null){
			return $this->generateRandomValues($x, $z, $sizeX, $sizeZ);
		}

		return $this->mergeValues($x, $z, $sizeX, $sizeZ);
	}

	/**
	 * Generates a rectangle, replacing all the positive values in the previous layer with random
	 * values from 2 to 31 while leaving zero and negative values unchanged.
	 *
	 * @param int $x the lowest x coordinate
	 * @param int $z the lowest z coordinate
	 * @param int $sizeX the x coordinate range
	 * @param int $sizeZ the z coordinate range
	 * @return int[] a flattened array of generated values
	 */
	public function generateRandomValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);
		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				$val = $values[$j + $i * $sizeX];
				if($val > 0){
					$this->setCoordsSeed($x + $j, $z + $i);
					$val = $this->nextInt(30) + 2;
				}
				$finalValues[$j + $i * $sizeX] = $val;
			}
		}

		return $finalValues;
	}

	/**
	 * Generates a rectangle using the previous layer and the variation layer.
	 *
	 * @param int $x the lowest x coordinate
	 * @param int $z the lowest z coordinate
	 * @param int $sizeX the x coordinate range
	 * @param int $sizeZ the z coordinate range
	 * @return int[] a flattened array of generated values
	 */
	public function mergeValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$gridX = $x - 1;
		$gridZ = $z - 1;
		$gridSizeX = $sizeX + 2;
		$gridSizeZ = $sizeZ + 2;

		$values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);
		$variationValues = $this->variationLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				$this->setCoordsSeed($x + $j, $z + $i);
				$centerValue = $values[$j + 1 + ($i + 1) * $gridSizeX];
				$variationValue = $variationValues[$j + 1 + ($i + 1) * $gridSizeX];
				if($centerValue !== 0 && $variationValue === 3 && $centerValue < 128){
					$finalValues[$j + $i * $sizeX] = isset(self::$BIOMES[$centerValue + 128]) ? $centerValue + 128 : $centerValue;
				}elseif($variationValue === 2 || $this->nextInt(3) === 0){
					$val = $centerValue;
					if(isset(self::$VARIATIONS[$centerValue])){
						$val = self::$VARIATIONS[$centerValue][$this->nextInt(count(self::$VARIATIONS[$centerValue]))];
					}elseif($centerValue === BiomeIds::DEEP_OCEAN && $this->nextInt(3) === 0){
						$val = self::$ISLANDS[$this->nextInt(count(self::$ISLANDS))];
					}
					if($variationValue === 2 && $val !== $centerValue){
						$val = isset(self::$BIOMES[$val + 128]) ? $val + 128 : $centerValue;
					}
					if($val !== $centerValue){
						$count = 0;
						if($values[$j + 1 + $i * $gridSizeX] === $centerValue){ // upper value
							++$count;
						}
						if($values[$j + 1 + ($i + 2) * $gridSizeX] === $centerValue){ // lower value
							++$count;
						}
						if($values[$j + ($i + 1) * $gridSizeX] === $centerValue){ // left value
							++$count;
						}
						if($values[$j + 2 + ($i + 1) * $gridSizeX] === $centerValue){ // right value
							++$count;
						}
						// spread mountains if not too close from an edge
						$finalValues[$j + $i * $sizeX] = $count < 3 ? $centerValue : $val;
					}else{
						$finalValues[$j + $i * $sizeX] = $val;
					}
				}else{
					$finalValues[$j + $i * $sizeX] = $centerValue;
				}
			}
		}

		return $finalValues;
	}
}

BiomeVariationMapLayer::init();