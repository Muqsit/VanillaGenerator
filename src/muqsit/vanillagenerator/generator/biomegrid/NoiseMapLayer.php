<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\noise\bukkit\SimplexOctaveGenerator;
use pocketmine\utils\Random;

class NoiseMapLayer extends MapLayer{

	private SimplexOctaveGenerator $noise_gen;

	public function __construct(int $seed){
		parent::__construct($seed);
		// Adjusted noise for 1.18+ style: more octaves for better continental structure
		$this->noise_gen = new SimplexOctaveGenerator(new Random($seed), 3);
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				// Updated for 1.18+ style generation with less ocean coverage
				// Improved noise sampling for better continental structure
				$noise = $this->noise_gen->octaveNoise($x + $j, $z + $i, 0, 0.2, 0.75, true) * 3.5;
				$val = 0;
				if($noise >= -0.1){ // Much lower ocean threshold (was 0.05)
					$val = $noise <= 0.15 ? 3 : 2; // Adjusted land threshold (was 0.2)
				}else{
					$this->setCoordsSeed($x + $j, $z + $i);
					// Reduced ocean probability for remaining areas (30% chance instead of 50%)
					$val = $this->nextInt(10) < 3 ? 0 : 3;
				}
				$values[$j + $i * $size_x] = $val;
			}
		}
		return $values;
	}
}