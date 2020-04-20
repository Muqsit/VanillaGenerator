<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

class WhittakerMapLayer extends MapLayer{

	public const WARM_WET = 0;
	public const COLD_DRY = 1;
	public const LARGER_BIOMES = 2;

	/** @var Climate[] */
	private static $MAP = [];

	public static function init() : void{
		self::$MAP[self::WARM_WET] = new Climate(2, [3, 1], 4);
		self::$MAP[self::COLD_DRY] = new Climate(3, [2, 4], 1);
	}

	/** @var MapLayer */
	private $belowLayer;

	/** @var int */
	private $type;


	public function __construct(int $seed, MapLayer $belowLayer, int $type){
		parent::__construct($seed);
		$this->belowLayer = $belowLayer;
		$this->type = $type;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		if($this->type === self::WARM_WET || $this->type === self::COLD_DRY){
			return $this->swapValues($x, $z, $sizeX, $sizeZ);
		}

		return $this->modifyValues($x, $z, $sizeX, $sizeZ);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $sizeX
	 * @param int $sizeZ
	 * @return int[]
	 */
	private function swapValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$gridX = $x - 1;
		$gridZ = $z - 1;
		$gridSizeX = $sizeX + 2;
		$gridSizeZ = $sizeZ + 2;
		$values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

		$climate = self::$MAP[$this->type];
		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				$centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
				if($centerVal === $climate->value){
					$upperVal = $values[$j + 1 + $i * $gridSizeX];
					$lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
					$leftVal = $values[$j + ($i + 1) * $gridSizeX];
					$rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];
					foreach($climate->crossTypes as $type){
						if(($upperVal === $type) || ($lowerVal === $type) || ($leftVal === $type) || ($rightVal === $type)){
							$centerVal = $climate->finalValue;
							break;
						}
					}
				}

				$finalValues[$j + $i * $sizeX] = $centerVal;
			}
		}

		return $finalValues;
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $sizeX
	 * @param int $sizeZ
	 * @return int[]
	 */
	private function modifyValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$values = $this->belowLayer->generateValues($x, $z, $sizeX, $sizeZ);
		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				$val = $values[$j + $i * $sizeX];
				if($val !== 0){
					$this->setCoordsSeed($x + $j, $z + $i);
					if($this->nextInt(13) === 0){
						$val += 1000;
					}
				}

				$finalValues[$j + $i * $sizeX] = $val;
			}
		}
		return $finalValues;
	}
}

class Climate{

	/** @var int */
	public $value;

	/** @var int[] */
	public $crossTypes;

	/** @var int */
	public $finalValue;

	/**
	 * @param int $value
	 * @param int[] $crossTypes
	 * @param int $finalValue
	 */
	public function __construct(int $value, array $crossTypes, int $finalValue){
		$this->value = $value;
		$this->crossTypes = $crossTypes;
		$this->finalValue = $finalValue;
	}
}

WhittakerMapLayer::init();