<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

use muqsit\vanillagenerator\generator\biomegrid\utils\MapLayerPair;
use muqsit\vanillagenerator\generator\Environment;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\WorldType;
use pocketmine\utils\Random;

abstract class MapLayer{

	public static function initialize(int $seed, int $environment, string $world_type) : MapLayerPair{
		if($environment === Environment::OVERWORLD && $world_type === WorldType::FLAT){
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::PLAINS), null);
		}

		if($environment === Environment::NETHER){
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::HELL), null);
		}

		if($environment === Environment::THE_END){
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::SKY), null);
		}


		$zoom = 2;
		if($world_type === WorldType::LARGE_BIOMES){
			$zoom = 4;
		}

		$layer = new NoiseMapLayer($seed); // this is initial land spread layer
		$layer = new WhittakerMapLayer($seed + 1, $layer, WhittakerMapLayer::WARM_WET);
		$layer = new WhittakerMapLayer($seed + 1, $layer, WhittakerMapLayer::COLD_DRY);
		$layer = new WhittakerMapLayer($seed + 2, $layer, WhittakerMapLayer::LARGER_BIOMES);

		for($i = 0; $i < 2; ++$i){
			$layer = new ZoomMapLayer($seed + 100 + $i, $layer, ZoomMapLayer::BLURRY);
		}

		for($i = 0; $i < 2; ++$i){
			$layer = new ErosionMapLayer($seed + 3 + $i, $layer);
		}

		$layer = new DeepOceanMapLayer($seed + 4, $layer);

		$layer_mountains = new BiomeVariationMapLayer($seed + 200, $layer);
		for($i = 0; $i < 2; ++$i){
			$layer_mountains = new ZoomMapLayer($seed + 200 + $i, $layer_mountains);
		}

		$layer = new BiomeMapLayer($seed + 5, $layer);
		for($i = 0; $i < 2; ++$i){
			$layer = new ZoomMapLayer($seed + 200 + $i, $layer);
		}

		$layer = new BiomeEdgeMapLayer($seed + 200, $layer);
		$layer = new BiomeVariationMapLayer($seed + 200, $layer, $layer_mountains);
		$layer = new RarePlainsMapLayer($seed + 201, $layer);
		$layer = new ZoomMapLayer($seed + 300, $layer);
		$layer = new ErosionMapLayer($seed + 6, $layer);
		$layer = new ZoomMapLayer($seed + 400, $layer);
		$layer = new BiomeThinEdgeMapLayer($seed + 400, $layer);
		$layer = new ShoreMapLayer($seed + 7, $layer);
		for($i = 0; $i < $zoom; ++$i){
			$layer = new ZoomMapLayer($seed + 500 + $i, $layer);
		}

		$layer_river = $layer_mountains;
		$layer_river = new ZoomMapLayer($seed + 300, $layer_river);
		$layer_river = new ZoomMapLayer($seed + 400, $layer_river);
		for($i = 0; $i < $zoom; ++$i){
			$layer_river = new ZoomMapLayer($seed + 500 + $i, $layer_river);
		}
		$layer_river = new RiverMapLayer($seed + 10, $layer_river);
		$layer = new RiverMapLayer($seed + 1000, $layer_river, $layer);

		$layer_lower_res = $layer;
		for($i = 0; $i < 2; ++$i){
			$layer = new ZoomMapLayer($seed + 2000 + $i, $layer);
		}

		$layer = new SmoothMapLayer($seed + 1001, $layer);

		return new MapLayerPair($layer, $layer_lower_res);
	}

	private Random $random;
	private int $seed;

	public function __construct(int $seed){
		$this->random = new Random();
		$this->seed = $seed;
	}

	public function setCoordsSeed(int $x, int $z) : void{
		$this->random->setSeed($this->seed);
		$this->random->setSeed($x * $this->random->nextInt() + $z * $this->random->nextInt() ^ $this->seed);
	}

	public function nextInt(int $max) : int{
		return $this->random->nextBoundedInt($max);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $size_x
	 * @param int $size_z
	 * @return int[]
	 */
	abstract public function generateValues(int $x, int $z, int $size_x, int $size_z) : array;
}