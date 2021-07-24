<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\biomegrid\utils\BiomeEdgeEntry;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use function array_key_exists;

class BiomeEdgeMapLayer extends MapLayer{

	/** @var int[] */
	private static array $MESA_EDGES = [
		BiomeIds::MESA_PLATEAU_STONE => BiomeIds::MESA,
		BiomeIds::MESA_PLATEAU => BiomeIds::MESA
	];

	/** @var int[] */
	private static array $MEGA_TAIGA_EDGES = [
		BiomeIds::MEGA_TAIGA => BiomeIds::TAIGA
	];

	/** @var int[] */
	private static array $DESERT_EDGES = [
		BiomeIds::DESERT => BiomeIdS::EXTREME_HILLS_PLUS_TREES
	];

	/** @var int[] */
	private static array $SWAMP1_EDGES = [
		BiomeIds::SWAMPLAND => BiomeIds::PLAINS
	];

	/** @var int[] */
	private static array $SWAMP2_EDGES = [
		BiomeIds::SWAMPLAND => BiomeIds::JUNGLE_EDGE
	];

	/** @var BiomeEdgeEntry[] */
	private static array $EDGES;

	public static function init() : void{
		self::$EDGES = [
			new BiomeEdgeEntry(self::$MESA_EDGES),
			new BiomeEdgeEntry(self::$MEGA_TAIGA_EDGES),
			new BiomeEdgeEntry(self::$DESERT_EDGES, [BiomeIds::ICE_PLAINS]),
			new BiomeEdgeEntry(self::$SWAMP1_EDGES, [BiomeIds::DESERT, BiomeIds::COLD_TAIGA, BiomeIds::ICE_PLAINS]),
			new BiomeEdgeEntry(self::$SWAMP2_EDGES, [BiomeIds::JUNGLE])
		];
	}

	private MapLayer $below_layer;

	public function __construct(int $seed, MapLayer $below_layer){
		parent::__construct($seed);
		$this->below_layer = $below_layer;
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$grid_x = $x - 1;
		$grid_z = $z - 1;
		$grid_size_x = $size_x + 2;
		$grid_size_z = $size_z + 2;
		$values = $this->below_layer->generateValues($grid_x, $grid_z, $grid_size_x, $grid_size_z);

		$final_values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				// This applies biome large edges using Von Neumann neighborhood
				$center_val = $values[$j + 1 + ($i + 1) * $grid_size_x];
				$val = $center_val;
				foreach(self::$EDGES as $edge){ // [$map, $entry]
					if(array_key_exists($center_val, $edge->key)){
						$upper_val = $values[$j + 1 + $i * $grid_size_x];
						$lower_val = $values[$j + 1 + ($i + 2) * $grid_size_x];
						$left_val = $values[$j + ($i + 1) * $grid_size_x];
						$right_val = $values[$j + 2 + ($i + 1) * $grid_size_x];

						if($edge->value === null && (
							!array_key_exists($upper_val, $edge->key)
							|| !array_key_exists($lower_val, $edge->key)
							|| !array_key_exists($left_val, $edge->key)
							|| !array_key_exists($right_val, $edge->key)
						)){
							$val = $edge->key[$center_val];
							break;
						}

						if($edge->value !== null && (
							array_key_exists($upper_val, $edge->value) ||
							array_key_exists($lower_val, $edge->value) ||
							array_key_exists($left_val, $edge->value) ||
							array_key_exists($right_val, $edge->value)
						)){
							$val = $edge->key[$center_val];
							break;
						}
					}
				}

				$final_values[$j + $i * $size_x] = $val;
			}
		}

		return $final_values;
	}
}

BiomeEdgeMapLayer::init();