<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\noise\bukkit\SimplexOctaveGenerator;
use pocketmine\utils\Random;

class NoiseMapLayer extends MapLayer{

	private SimplexOctaveGenerator $noise_gen;

	public function __construct(int $seed){
		parent::__construct($seed);
		$this->noise_gen = new SimplexOctaveGenerator(new Random($seed), 3);
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				$noise = $this->noise_gen->octaveNoise($x + $j, $z + $i, 0, 0.2, 0.75, true) * 3.5;
				$val = 0;
				if($noise >= -0.1){
					$val = $noise <= 0.15 ? 3 : 2;
				}else{
					$this->setCoordsSeed($x + $j, $z + $i);
					$val = $this->nextInt(10) < 3 ? 0 : 3;
				}
				$values[$j + $i * $size_x] = $val;
			}
		}
		return $values;
	}
}