<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class BiomeEdgeMapLayer extends MapLayer{

	/** @var int[] */
	private static $MESA_EDGES = [
		BiomeIds::MESA_ROCK => BiomeIds::MESA,
		BiomeIds::MESA_CLEAR_ROCK => BiomeIds::MESA
	];

	/** @var int[] */
	private static $MEGA_TAIGA_EDGES = [
		BiomeIds::REDWOOD_TAIGA => BiomeIds::TAIGA
	];

	/** @var int[] */
	private static $DESERT_EDGES = [
		BiomeIds::DESERT => BiomeIdS::EXTREME_HILLS_WITH_TREES
	];

	/** @var int[] */
	private static $SWAMP1_EDGES = [
		BiomeIds::SWAMPLAND => BiomeIds::PLAINS
	];

	/** @var int[] */
	private static $SWAMP2_EDGES = [
		BiomeIds::SWAMPLAND => BiomeIds::JUNGLE_EDGE
	];

	/** @var int[][]|int[][][]|null[][] */
	private static $EDGES;

	public static function init() : void{
		self::$EDGES = [
			[self::$MESA_EDGES, null],
			[self::$MEGA_TAIGA_EDGES, null],
			[self::$DESERT_EDGES, [BiomeIds::ICE_FLATS]],
			[self::$SWAMP1_EDGES, [BiomeIds::DESERT, BiomeIds::TAIGA_COLD, BiomeIds::ICE_FLATS]],
			[self::$SWAMP2_EDGES, [BiomeIds::JUNGLE]]
		];
	}

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
				// This applies biome large edges using Von Neumann neighborhood
				$centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
				$val = $centerVal;
				foreach(self::$EDGES as [$map, $entry]){
					if(isset($map[$centerVal])){
						$upperVal = $values[$j + 1 + $i * $gridSizeX];
						$lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
						$leftVal = $values[$j + ($i + 1) * $gridSizeX];
						$rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
						if($entry === null && (
								!isset($map[$upperVal])
								|| !isset($map[$lowerVal])
								|| !isset($map[$leftVal])
								|| !isset($map[$rightVal])
							)){
							$val = $map[$centerVal];
							break;
						}

						if($entry !== null && (
								in_array($upperVal, $entry, true)
								|| in_array($lowerVal, $entry, true)
								|| in_array($leftVal, $entry, true)
								|| in_array($rightVal, $entry, true)
							)){
							$val = $map[$centerVal];
							break;
						}
					}
				}

				$finalValues[$j + $i * $sizeX] = $val;
			}
		}

		return $finalValues;
	}
}

BiomeEdgeMapLayer::init();