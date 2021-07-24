<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use ReflectionClass;
use function array_key_exists;

class BiomeVariationMapLayer extends MapLayer{

	/** @var int[] */
	private static array $ISLANDS = [BiomeIds::PLAINS, BiomeIds::FOREST];

	/** @var int[][] */
	private static array $VARIATIONS = [
		BiomeIds::DESERT => [BiomeIds::DESERT_HILLS],
		BiomeIds::FOREST => [BiomeIds::FOREST_HILLS],
		BiomeIds::BIRCH_FOREST => [BiomeIds::BIRCH_FOREST_HILLS],
		BiomeIds::ROOFED_FOREST => [BiomeIds::PLAINS],
		BiomeIds::TAIGA => [BiomeIds::TAIGA_HILLS],
		BiomeIds::MEGA_TAIGA => [BiomeIds::MEGA_TAIGA_HILLS],
		BiomeIds::COLD_TAIGA => [BiomeIds::COLD_TAIGA_HILLS],
		BiomeIds::PLAINS => [BiomeIds::FOREST, BiomeIds::FOREST, BiomeIds::FOREST_HILLS],
		BiomeIds::ICE_PLAINS => [BiomeIds::ICE_MOUNTAINS],
		BiomeIds::JUNGLE => [BiomeIds::JUNGLE_HILLS],
		BiomeIds::OCEAN => [BiomeIds::DEEP_OCEAN],
		BiomeIds::EXTREME_HILLS => [BiomeIds::EXTREME_HILLS_PLUS_TREES],
		BiomeIds::SAVANNA => [BiomeIds::SAVANNA_PLATEAU],
		BiomeIds::MESA_PLATEAU_STONE => [BiomeIds::MESA],
		BiomeIds::MESA_PLATEAU => [BiomeIds::MESA],
		BiomeIds::MESA => [BiomeIds::MESA]
	];

	/** @var string[] */
	private static array $BIOMES;

	public static function init() : void{
		self::$BIOMES = [];
		foreach((new ReflectionClass(BiomeIds::class))->getConstants() as $const => $biomeId){
			self::$BIOMES[$biomeId] = $const;
		}
	}

	private MapLayer $below_layer;
	private ?MapLayer $variation_layer;

	public function __construct(int $seed, MapLayer $below_layer, ?MapLayer $variation_layer = null){
		parent::__construct($seed);
		$this->below_layer = $below_layer;
		$this->variation_layer = $variation_layer;
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		if($this->variation_layer === null){
			return $this->generateRandomValues($x, $z, $size_x, $size_z);
		}

		return $this->mergeValues($x, $z, $size_x, $size_z);
	}

	/**
	 * Generates a rectangle, replacing all the positive values in the previous layer with random
	 * values from 2 to 31 while leaving zero and negative values unchanged.
	 *
	 * @param int $x the lowest x coordinate
	 * @param int $z the lowest z coordinate
	 * @param int $size_x the x coordinate range
	 * @param int $size_z the z coordinate range
	 * @return int[] a flattened array of generated values
	 */
	public function generateRandomValues(int $x, int $z, int $size_x, int $size_z) : array{
		$values = $this->below_layer->generateValues($x, $z, $size_x, $size_z);
		$final_values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				$val = $values[$j + $i * $size_x];
				if($val > 0){
					$this->setCoordsSeed($x + $j, $z + $i);
					$val = $this->nextInt(30) + 2;
				}
				$final_values[$j + $i * $size_x] = $val;
			}
		}

		return $final_values;
	}

	/**
	 * Generates a rectangle using the previous layer and the variation layer.
	 *
	 * @param int $x the lowest x coordinate
	 * @param int $z the lowest z coordinate
	 * @param int $size_x the x coordinate range
	 * @param int $size_z the z coordinate range
	 * @return int[] a flattened array of generated values
	 */
	public function mergeValues(int $x, int $z, int $size_x, int $size_z) : array{
		$grid_x = $x - 1;
		$grid_z = $z - 1;
		$grid_size_x = $size_x + 2;
		$grid_size_z = $size_z + 2;

		$values = $this->below_layer->generateValues($grid_x, $grid_z, $grid_size_x, $grid_size_z);
		$variation_values = $this->variation_layer->generateValues($grid_x, $grid_z, $grid_size_x, $grid_size_z);

		$final_values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				$this->setCoordsSeed($x + $j, $z + $i);
				$center_value = $values[$j + 1 + ($i + 1) * $grid_size_x];
				$variation_value = $variation_values[$j + 1 + ($i + 1) * $grid_size_x];
				if($center_value !== 0 && $variation_value === 3 && $center_value < 128){
					$final_values[$j + $i * $size_x] = array_key_exists($center_value + 128, self::$BIOMES) ? $center_value + 128 : $center_value;
				}elseif($variation_value === 2 || $this->nextInt(3) === 0){
					$val = $center_value;
					if(array_key_exists($center_value, self::$VARIATIONS)){
						$val = self::$VARIATIONS[$center_value][$this->nextInt(count(self::$VARIATIONS[$center_value]))];
					}elseif($center_value === BiomeIds::DEEP_OCEAN && $this->nextInt(3) === 0){
						$val = self::$ISLANDS[$this->nextInt(count(self::$ISLANDS))];
					}
					if($variation_value === 2 && $val !== $center_value){
						$val = array_key_exists($val + 128, self::$BIOMES) ? $val + 128 : $center_value;
					}
					if($val !== $center_value){
						$count = 0;
						if($values[$j + 1 + $i * $grid_size_x] === $center_value){ // upper value
							++$count;
						}
						if($values[$j + 1 + ($i + 2) * $grid_size_x] === $center_value){ // lower value
							++$count;
						}
						if($values[$j + ($i + 1) * $grid_size_x] === $center_value){ // left value
							++$count;
						}
						if($values[$j + 2 + ($i + 1) * $grid_size_x] === $center_value){ // right value
							++$count;
						}
						// spread mountains if not too close from an edge
						$final_values[$j + $i * $size_x] = $count < 3 ? $center_value : $val;
					}else{
						$final_values[$j + $i * $size_x] = $val;
					}
				}else{
					$final_values[$j + $i * $size_x] = $center_value;
				}
			}
		}

		return $final_values;
	}
}

BiomeVariationMapLayer::init();