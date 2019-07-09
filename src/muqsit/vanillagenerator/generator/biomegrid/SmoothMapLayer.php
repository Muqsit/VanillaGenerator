<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

class SmoothMapLayer extends MapLayer{

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
				// This applies smoothing using Von Neumann neighborhood
				// it takes a 3x3 grid with a cross shape and analyzes values as follow
				// 0X0
				// XxX
				// 0X0
				// it is required that we use the same shape that was used for what we
				// want to smooth
				$upperVal = $values[$j + 1 + $i * $gridSizeX];
				$lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
				$leftVal = $values[$j + ($i + 1) * $gridSizeX];
				$rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
				$centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
				if($upperVal === $lowerVal && $leftVal === $rightVal){
					$this->setCoordsSeed($x + $j, $z + $i);
					$centerVal = $this->nextInt(2) === 0 ? $upperVal : $leftVal;
				}elseif($upperVal === $lowerVal){
					$centerVal = $upperVal;
				}elseif($leftVal === $rightVal){
					$centerVal = $leftVal;
				}

				$finalValues[$j + $i * $sizeX] = $centerVal;
			}
		}

		return $finalValues;
	}
}