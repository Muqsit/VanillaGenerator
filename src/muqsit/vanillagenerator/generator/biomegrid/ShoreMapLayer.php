<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class ShoreMapLayer extends MapLayer{

	/** @var int[] */
	private static $OCEANS = [BiomeIds::OCEAN => 0, BiomeIds::DEEP_OCEAN => 0];

	/** @var int[] */
	private static $SPECIAL_SHORES = [
		BiomeIds::EXTREME_HILLS => BiomeIds::STONE_BEACH,
		BiomeIds::EXTREME_HILLS_WITH_TREES => BiomeIds::STONE_BEACH,
		BiomeIds::MUTATED_EXTREME_HILLS => BiomeIds::STONE_BEACH,
		BiomeIds::MUTATED_EXTREME_HILLS_WITH_TREES => BiomeIds::STONE_BEACH,
		BiomeIds::ICE_FLATS => BiomeIds::COLD_BEACH,
		BiomeIds::ICE_MOUNTAINS => BiomeIds::COLD_BEACH,
		BiomeIds::MUTATED_ICE_FLATS => BiomeIds::COLD_BEACH,
		BiomeIds::TAIGA_COLD => BiomeIds::COLD_BEACH,
		BiomeIds::TAIGA_COLD_HILLS => BiomeIds::COLD_BEACH,
		BiomeIds::MUTATED_TAIGA_COLD => BiomeIds::COLD_BEACH,
		BiomeIds::MUSHROOM_ISLAND => BiomeIds::MUSHROOM_ISLAND_SHORE,
		BiomeIds::SWAMPLAND => BiomeIds::SWAMPLAND,
		BiomeIds::MESA => BiomeIds::MESA,
		BiomeIds::MESA_ROCK => BiomeIds::MESA_ROCK,
		BiomeIds::MUTATED_MESA_ROCK => BiomeIds::MUTATED_MESA_ROCK,
		BiomeIds::MESA_CLEAR_ROCK => BiomeIds::MESA_CLEAR_ROCK,
		BiomeIds::MUTATED_MESA_CLEAR_ROCK => BiomeIds::MUTATED_MESA_CLEAR_ROCK,
		BiomeIds::MUTATED_MESA => BiomeIds::MUTATED_MESA
	];

	/** @var MapLayer */
	private $belowLayer;

	public function __construct(int $seed, MapLayer $belowLayer){
		parent::__construct($seed);
		$this->belowLayer = $belowLayer;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$gridX = $x - 1;
		$gridZ = $z - 1;
		$gridSizeX = $sizeX + 2;
		$gridSizeZ = $sizeZ + 2;
		$values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				// This applies shores using Von Neumann neighborhood
				// it takes a 3x3 grid with a cross shape and analyzes values as follow
				// 0X0
				// XxX
				// 0X0
				// the grid center value decides how we are proceeding:
				// - if it's not ocean and it's surrounded by at least 1 ocean cell
				// it turns the center value into beach.
				$upperVal = $values[$j + 1 + $i * $gridSizeX];
				$lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
				$leftVal = $values[$j + ($i + 1) * $gridSizeX];
				$rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
				$centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
				if(!array_key_exists($centerVal, self::$OCEANS) && (
						array_key_exists($upperVal, self::$OCEANS) || array_key_exists($lowerVal, self::$OCEANS)
						|| array_key_exists($leftVal, self::$OCEANS) || array_key_exists($rightVal, self::$OCEANS)
					)){
					$finalValues[$j + $i * $sizeX] = self::$SPECIAL_SHORES[$centerVal] ?? BiomeIds::BEACH;
				}else{
					$finalValues[$j + $i * $sizeX] = $centerVal;
				}
			}
		}
		return $finalValues;
	}
}