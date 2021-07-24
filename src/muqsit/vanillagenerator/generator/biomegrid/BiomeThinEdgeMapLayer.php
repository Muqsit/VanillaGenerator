<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\biomegrid\utils\BiomeEdgeEntry;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use function array_key_exists;

class BiomeThinEdgeMapLayer extends MapLayer{

	/** @var int[] */
	private static array $OCEANS = [BiomeIds::OCEAN, BiomeIds::DEEP_OCEAN];

	/** @var int[] */
	private static array $MESA_EDGES = [
		BiomeIds::MESA => BiomeIds::DESERT,
		BiomeIds::MESA_BRYCE => BiomeIds::DESERT,
		BiomeIds::MESA_PLATEAU_STONE => BiomeIds::DESERT,
		BiomeIds::MESA_PLATEAU_STONE_MUTATED => BiomeIds::DESERT,
		BiomeIds::MESA_PLATEAU => BiomeIds::DESERT,
		BiomeIds::MESA_PLATEAU_MUTATED => BiomeIds::DESERT
	];

	/** @var int[] */
	private static array $JUNGLE_EDGES = [
		BiomeIds::JUNGLE => BiomeIds::JUNGLE_EDGE,
		BiomeIds::JUNGLE_HILLS => BiomeIds::JUNGLE_EDGE,
		BiomeIds::JUNGLE_MUTATED => BiomeIds::JUNGLE_EDGE,
		BiomeIds::JUNGLE_EDGE_MUTATED => BiomeIds::JUNGLE_EDGE
	];

	/** @var BiomeEdgeEntry[] */
	private static array $EDGES;

	public static function init() : void{
		self::$OCEANS = array_flip(self::$OCEANS);
		self::$EDGES = [
			new BiomeEdgeEntry(self::$MESA_EDGES),
			new BiomeEdgeEntry(self::$JUNGLE_EDGES, [BiomeIds::JUNGLE, BiomeIds::JUNGLE_HILLS, BiomeIds::JUNGLE_MUTATED, BiomeIds::JUNGLE_EDGE_MUTATED, BiomeIds::FOREST, BiomeIds::TAIGA])
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
				// This applies biome thin edges using Von Neumann neighborhood
				$center_val = $values[$j + 1 + ($i + 1) * $grid_size_x];
				$val = $center_val;
				foreach(self::$EDGES as $edge){
					if(array_key_exists($center_val, $edge->key)){
						$upper_val = $values[$j + 1 + $i * $grid_size_x];
						$lower_val = $values[$j + 1 + ($i + 2) * $grid_size_x];
						$left_val = $values[$j + ($i + 1) * $grid_size_x];
						$right_val = $values[$j + 2 + ($i + 1) * $grid_size_x];
						if($edge->value === null && (
							(!array_key_exists($upper_val, self::$OCEANS) && !array_key_exists($upper_val, $edge->key))
							|| (!array_key_exists($lower_val, self::$OCEANS) && !array_key_exists($lower_val, $edge->key))
							|| (!array_key_exists($left_val, self::$OCEANS) && !array_key_exists($left_val, $edge->key))
							|| (!array_key_exists($right_val, self::$OCEANS) && !array_key_exists($right_val, $edge->key))
						)){
							$val = $edge->key[$center_val];
							break;
						}
						if($edge->value !== null && (
							(!array_key_exists($upper_val, self::$OCEANS) && !array_key_exists($upper_val, $edge->value))
							|| (!array_key_exists($lower_val, self::$OCEANS) && !array_key_exists($lower_val, $edge->value))
							|| (!array_key_exists($left_val, self::$OCEANS) && !array_key_exists($left_val, $edge->value))
							|| (!array_key_exists($right_val, self::$OCEANS) && !array_key_exists($right_val, $edge->value))
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

BiomeThinEdgeMapLayer::init();