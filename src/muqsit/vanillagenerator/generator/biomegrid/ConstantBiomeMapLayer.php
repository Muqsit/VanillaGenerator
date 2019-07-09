<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

class ConstantBiomeMapLayer extends MapLayer{

	/** @var int */
	private $biome;

	public function __construct(int $seed, int $biome){
		parent::__construct($seed);
		$this->biome = $biome;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$values = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				$values[$j + $i * $sizeX] = $this->biome;
			}
		}

		return $values;
	}
}