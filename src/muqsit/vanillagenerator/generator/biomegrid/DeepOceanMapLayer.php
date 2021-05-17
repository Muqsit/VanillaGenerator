<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class DeepOceanMapLayer extends MapLayer{

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
				// This applies deep oceans using Von Neumann neighborhood
				// it takes a 3x3 grid with a cross shape and analyzes values as follow
				// 0X0
				// XxX
				// 0X0
				// the grid center value decides how we are proceeding:
				// - if it's ocean and it's surrounded by 4 ocean cells we spread deep ocean.
				$center_val = $values[$j + 1 + ($i + 1) * $grid_size_x];
				if($center_val === 0){
					$upper_val = $values[$j + 1 + $i * $grid_size_x];
					$lower_val = $values[$j + 1 + ($i + 2) * $grid_size_x];
					$left_val = $values[$j + ($i + 1) * $grid_size_x];
					$right_val = $values[$j + 2 + ($i + 1) * $grid_size_x];
					if($upper_val === 0 && $lower_val === 0 && $left_val === 0 && $right_val === 0){
						$this->setCoordsSeed($x + $j, $z + $i);
						$final_values[$j + $i * $size_x] = $this->nextInt(100) === 0 ? BiomeIds::MUSHROOM_ISLAND : BiomeIds::DEEP_OCEAN;
					}else{
						$final_values[$j + $i * $size_x] = $center_val;
					}
				}else{
					$final_values[$j + $i * $size_x] = $center_val;
				}
			}
		}
		return $final_values;
	}
}