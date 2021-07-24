<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use function array_key_exists;

class RarePlainsMapLayer extends MapLayer{

	/** @var int[] */
	private static array $RARE_PLAINS = [BiomeIds::PLAINS => BiomeIds::SUNFLOWER_PLAINS];

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
				$this->setCoordsSeed($x + $j, $z + $i);
				$center_value = $values[$j + 1 + ($i + 1) * $grid_size_x];
				if($this->nextInt(57) === 0 && array_key_exists($center_value, self::$RARE_PLAINS)){
					$center_value = self::$RARE_PLAINS[$center_value];
				}

				$final_values[$j + $i * $size_x] = $center_value;
			}
		}

		return $final_values;
	}
}