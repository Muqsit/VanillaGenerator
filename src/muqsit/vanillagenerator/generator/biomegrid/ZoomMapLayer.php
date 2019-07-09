<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

class ZoomMapLayer extends MapLayer{

	public const NORMAL = 0;
	public const BLURRY = 1;

	/** @var MapLayer */
	private $belowLayer;

	/** @var int */
	private $zoomType;

	public function __construct(int $seed, MapLayer $belowLayer, int $zoomType = self::NORMAL){
		parent::__construct($seed);
		$this->belowLayer = $belowLayer;
		$this->zoomType = $zoomType;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$gridX = $x >> 1;
		$gridZ = $z >> 1;
		$gridSizeX = ($sizeX >> 1) + 2;
		$gridSizeZ = ($sizeZ >> 1) + 2;
		$values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

		$zoomSizeX = $gridSizeX - 1 << 1;
		// $zoomSizeZ = $gridSizeZ - 1 << 1;
		$tmpValues = [];
		for($i = 0; $i < $gridSizeZ - 1; ++$i){
			$n = $i * 2 * $zoomSizeX;
			$upperLeftVal = $values[$i * $gridSizeX];
			$lowerLeftVal = $values[($i + 1) * $gridSizeX];
			for($j = 0; $j < $gridSizeX - 1; ++$j){
				$this->setCoordsSeed($gridX + $j << 1, $gridZ + $i << 1);
				$tmpValues[$n] = $upperLeftVal;
				$tmpValues[$n + $zoomSizeX] = $this->nextInt(2) > 0 ? $upperLeftVal : $lowerLeftVal;
				$upperRightVal = $values[$j + 1 + $i * $gridSizeX];
				$lowerRightVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
				$tmpValues[$n + 1] = $this->nextInt(2) > 0 ? $upperLeftVal : $upperRightVal;
				$tmpValues[$n + 1 + $zoomSizeX] = $this->getNearest($upperLeftVal, $upperRightVal, $lowerLeftVal, $lowerRightVal);
				$upperLeftVal = $upperRightVal;
				$lowerLeftVal = $lowerRightVal;
				$n += 2;
			}
		}

		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				$finalValues[$j + $i * $sizeX] = $tmpValues[$j + ($i + ($z & 1)) * $zoomSizeX + ($x & 1)];
			}
		}

		return $finalValues;
	}

	private function getNearest(int $upperLeftVal, int $upperRightVal, int $lowerLeftVal, int $lowerRightVal) : int{
		if($this->zoomType === self::NORMAL){
			if($upperRightVal === $lowerLeftVal && $lowerLeftVal === $lowerRightVal){
				return $upperRightVal;
			}
			if($upperLeftVal === $upperRightVal && $upperLeftVal === $lowerLeftVal){
				return $upperLeftVal;
			}
			if($upperLeftVal === $upperRightVal && $upperLeftVal === $lowerRightVal){
				return $upperLeftVal;
			}
			if($upperLeftVal === $lowerLeftVal && $upperLeftVal === $lowerRightVal){
				return $upperLeftVal;
			}
			if($upperLeftVal === $upperRightVal && $lowerLeftVal !== $lowerRightVal){
				return $upperLeftVal;
			}
			if($upperLeftVal === $lowerLeftVal && $upperRightVal !== $lowerRightVal){
				return $upperLeftVal;
			}
			if($upperLeftVal === $lowerRightVal && $upperRightVal !== $lowerLeftVal){
				return $upperLeftVal;
			}
			if($upperRightVal === $lowerLeftVal && $upperLeftVal !== $lowerRightVal){
				return $upperRightVal;
			}
			if($upperRightVal === $lowerRightVal && $upperLeftVal !== $lowerLeftVal){
				return $upperRightVal;
			}
			if($lowerLeftVal === $lowerRightVal && $upperLeftVal !== $upperRightVal){
				return $lowerLeftVal;
			}
		}

		$values = [$upperLeftVal, $upperRightVal, $lowerLeftVal, $lowerRightVal];
		return $values[$this->nextInt(count($values))];
	}
}