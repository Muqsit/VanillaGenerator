<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use function array_key_exists;

class RiverMapLayer extends MapLayer{

	/** @var int[] */
	private static $OCEANS = [BiomeIds::OCEAN => 0, BiomeIds::DEEP_OCEAN => 0];

	/** @var int[] */
	private static $SPECIAL_RIVERS = [
		BiomeIds::ICE_FLATS => BiomeIds::FROZEN_RIVER,
		BiomeIds::MUSHROOM_ISLAND => BiomeIds::MUSHROOM_ISLAND_SHORE,
		BiomeIds::MUSHROOM_ISLAND_SHORE => BiomeIds::MUSHROOM_ISLAND_SHORE
	];

	/** @var int */
	private static $CLEAR_VALUE = 0;

	/** @var int */
	private static $RIVER_VALUE = 1;

	/** @var MapLayer */
	private $belowLayer;

	/** @var MapLayer */
	private $mergeLayer;

	public function __construct(int $seed, MapLayer $belowLayer, ?MapLayer $mergeLayer = null){
		parent::__construct($seed);
		$this->belowLayer = $belowLayer;
		$this->mergeLayer = $mergeLayer;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		if($this->mergeLayer === null){
			return $this->generateRivers($x, $z, $sizeX, $sizeZ);
		}

		return $this->mergeRivers($x, $z, $sizeX, $sizeZ);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $sizeX
	 * @param int $sizeZ
	 * @return int[]
	 */
	private function generateRivers(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$gridX = $x - 1;
		$gridZ = $z - 1;
		$gridSizeX = $sizeX + 2;
		$gridSizeZ = $sizeZ + 2;

		$values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);
		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				// This applies rivers using Von Neumann neighborhood
				$centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX] & 1;
				$upperVal = $values[$j + 1 + $i * $gridSizeX] & 1;
				$lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX] & 1;
				$leftVal = $values[$j + ($i + 1) * $gridSizeX] & 1;
				$rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX] & 1;
				$val = self::$CLEAR_VALUE;
				if($centerVal !== $upperVal || $centerVal !== $lowerVal || $centerVal !== $leftVal || $centerVal !== $rightVal){
					$val = self::$RIVER_VALUE;
				}
				$finalValues[$j + $i * $sizeX] = $val;
			}
		}
		return $finalValues;
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $sizeX
	 * @param int $sizeZ
	 * @return int[]
	 */
	private function mergeRivers(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);
		$mergeValues = $this->mergeLayer->generateValues($x, $z, $sizeX, $sizeZ);

		$finalValues = [];
		for($i = 0; $i < $sizeX * $sizeZ; ++$i){
			$val = $mergeValues[$i];
			if(array_key_exists($mergeValues[$i], self::$OCEANS)){
				$val = $mergeValues[$i];
			}elseif($values[$i] === self::$RIVER_VALUE){
				$val = self::$SPECIAL_RIVERS[$mergeValues[$i]] ?? BiomeIds::RIVER;
			}
			$finalValues[$i] = $val;
		}

		return $finalValues;
	}
}