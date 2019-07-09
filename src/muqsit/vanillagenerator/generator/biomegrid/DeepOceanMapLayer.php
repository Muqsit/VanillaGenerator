<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class DeepOceanMapLayer extends MapLayer{

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
				// This applies deep oceans using Von Neumann neighborhood
				// it takes a 3x3 grid with a cross shape and analyzes values as follow
				// 0X0
				// XxX
				// 0X0
				// the grid center value decides how we are proceeding:
				// - if it's ocean and it's surrounded by 4 ocean cells we spread deep ocean.
				$centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
				if($centerVal === 0){
					$upperVal = $values[$j + 1 + $i * $gridSizeX];
					$lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
					$leftVal = $values[$j + ($i + 1) * $gridSizeX];
					$rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
					if($upperVal === 0 && $lowerVal === 0 && $leftVal === 0 && $rightVal === 0){
						$this->setCoordsSeed($x + $j, $z + $i);
						$finalValues[$j + $i * $sizeX] = $this->nextInt(100) === 0 ? BiomeIds::MUSHROOM_ISLAND : BiomeIds::DEEP_OCEAN;
					}else{
						$finalValues[$j + $i * $sizeX] = $centerVal;
					}
				}else{
					$finalValues[$j + $i * $sizeX] = $centerVal;
				}
			}
		}
		return $finalValues;
	}
}