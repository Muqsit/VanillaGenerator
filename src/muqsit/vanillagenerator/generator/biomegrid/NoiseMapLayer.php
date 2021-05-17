<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\noise\bukkit\SimplexOctaveGenerator;
use pocketmine\utils\Random;

class NoiseMapLayer extends MapLayer{

	private SimplexOctaveGenerator $noise_gen;

	public function __construct(int $seed){
		parent::__construct($seed);
		$this->noise_gen = new SimplexOctaveGenerator(new Random($seed), 2);
	}

	public function generateValues(int $x, int $z, int $size_x, int $size_z) : array{
		$values = [];
		for($i = 0; $i < $size_z; ++$i){
			for($j = 0; $j < $size_x; ++$j){
				$noise = $this->noise_gen->octaveNoise($x + $j, $z + $i, 0, 0.175, 0.8, true) * 4.0;
				$val = 0;
				if($noise >= 0.05){
					$val = $noise <= 0.2 ? 3 : 2;
				}else{
					$this->setCoordsSeed($x + $j, $z + $i);
					$val = $this->nextInt(2) === 0 ? 3 : 0;
				}
				$values[$j + $i * $size_x] = $val;
				//$values[$j + $i * $size_x] =
				//        $noise >= -0.5
				//                ? (float) $noise >= 0.57
				//                        ? 2
				//                : $noise <= 0.2
				//                        ? 3
				//                        : 2
				//        : $this->nextInt(2) === 0
				//                        ? 3
				//                        : 0;
			}
		}
		return $values;
	}
}