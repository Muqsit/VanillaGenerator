<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

class ZoomMapLayer extends MapLayer{

	public const NORMAL = 0;
	public const BLURRY = 1;

	private MapLayer $below_layer;
	private int $zoom_type;

	public function __construct(int $seed, MapLayer $below_layer, int $zoom_type = self::NORMAL){
		parent::__construct($seed);
		$this->below_layer = $below_layer;
		$this->zoom_type = $zoom_type;
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$grid_x = $x >> 1;
		$grid_z = $z >> 1;
		$grid_size_x = ($size_x >> 1) + 2;
		$grid_size_z = ($size_z >> 1) + 2;
		$values = $this->below_layer->generateValues($grid_x, $grid_z, $grid_size_x, $grid_size_z);

		$zoom_size_x = $grid_size_x - 1 << 1;
		// $zoom_size_z = $grid_size_z - 1 << 1;
		$tmp_values = [];
		for($i = 0; $i < $grid_size_z - 1; ++$i){
			$n = $i * 2 * $zoom_size_x;
			$upper_left_val = $values[$i * $grid_size_x];
			$lower_left_val = $values[($i + 1) * $grid_size_x];
			for($j = 0; $j < $grid_size_x - 1; ++$j){
				$this->setCoordsSeed($grid_x + $j << 1, $grid_z + $i << 1);
				$tmp_values[$n] = $upper_left_val;
				$tmp_values[$n + $zoom_size_x] = $this->nextInt(2) > 0 ? $upper_left_val : $lower_left_val;
				$upper_right_val = $values[$j + 1 + $i * $grid_size_x];
				$lower_right_val = $values[$j + 1 + ($i + 1) * $grid_size_x];
				$tmp_values[$n + 1] = $this->nextInt(2) > 0 ? $upper_left_val : $upper_right_val;
				$tmp_values[$n + 1 + $zoom_size_x] = $this->getNearest($upper_left_val, $upper_right_val, $lower_left_val, $lower_right_val);
				$upper_left_val = $upper_right_val;
				$lower_left_val = $lower_right_val;
				$n += 2;
			}
		}

		$final_values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				$final_values[$j + $i * $size_x] = $tmp_values[$j + ($i + ($z & 1)) * $zoom_size_x + ($x & 1)];
			}
		}

		return $final_values;
	}

	private function getNearest(int $upper_left_val, int $upper_right_val, int $lower_left_val, int $lower_right_val) : int{
		if($this->zoom_type === self::NORMAL){
			if($upper_right_val === $lower_left_val && $lower_left_val === $lower_right_val){
				return $upper_right_val;
			}
			if($upper_left_val === $upper_right_val && $upper_left_val === $lower_left_val){
				return $upper_left_val;
			}
			if($upper_left_val === $upper_right_val && $upper_left_val === $lower_right_val){
				return $upper_left_val;
			}
			if($upper_left_val === $lower_left_val && $upper_left_val === $lower_right_val){
				return $upper_left_val;
			}
			if($upper_left_val === $upper_right_val && $lower_left_val !== $lower_right_val){
				return $upper_left_val;
			}
			if($upper_left_val === $lower_left_val && $upper_right_val !== $lower_right_val){
				return $upper_left_val;
			}
			if($upper_left_val === $lower_right_val && $upper_right_val !== $lower_left_val){
				return $upper_left_val;
			}
			if($upper_right_val === $lower_left_val && $upper_left_val !== $lower_right_val){
				return $upper_right_val;
			}
			if($upper_right_val === $lower_right_val && $upper_left_val !== $lower_left_val){
				return $upper_right_val;
			}
			if($lower_left_val === $lower_right_val && $upper_left_val !== $upper_right_val){
				return $lower_left_val;
			}
		}

		$values = [$upper_left_val, $upper_right_val, $lower_left_val, $lower_right_val];
		return $values[$this->nextInt(count($values))];
	}
}