<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

class ConstantBiomeMapLayer extends MapLayer{

	private int $biome;

	public function __construct(int $seed, int $biome){
		parent::__construct($seed);
		$this->biome = $biome;
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				$values[$j + $i * $size_x] = $this->biome;
			}
		}

		return $values;
	}
}