<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\utils\Random;

final class BiomeClimateManager{

	private static SimplexOctaveGenerator $noise_gen;
	private static BiomeClimate $default;

	/** @var BiomeClimate[] */
	private static array $climates = [];

	public static function init() : void{
		self::$noise_gen = SimplexOctaveGenerator::fromRandomAndOctaves(new Random(1234), 1, 0, 0, 0);
		self::$noise_gen->setScale(1 / 8.0);

		self::$default = new BiomeClimate(0.5, 0.5, true);

		self::register(new BiomeClimate(0.8, 0.4, true),
			BiomeIds::PLAINS,
			BiomeIds::SUNFLOWER_PLAINS,
			BiomeIds::BEACH
		);

		self::register(new BiomeClimate(2.0, 0.0, false),
			BiomeIds::DESERT,
			BiomeIds::DESERT_HILLS,
			BiomeIds::DESERT_MUTATED,
			BiomeIds::MESA,
			BiomeIds::MESA_BRYCE,
			BiomeIds::MESA_PLATEAU,
			BiomeIds::MESA_PLATEAU_STONE,
			BiomeIds::MESA_PLATEAU_MUTATED,
			BiomeIds::MESA_PLATEAU_STONE_MUTATED,
			BiomeIds::HELL
		);

		self::register(new BiomeClimate(0.2, 0.3, true),
			BiomeIds::EXTREME_HILLS,
			BiomeIds::EXTREME_HILLS_PLUS_TREES,
			BiomeIds::EXTREME_HILLS_MUTATED,
			BiomeIds::EXTREME_HILLS_PLUS_TREES_MUTATED,
			BiomeIds::STONE_BEACH,
			BiomeIds::EXTREME_HILLS_EDGE
		);

		self::register(new BiomeClimate(0.7, 0.8, true),
			BiomeIds::FOREST,
			BiomeIds::FOREST_HILLS,
			BiomeIds::FLOWER_FOREST,
			BiomeIds::ROOFED_FOREST,
			BiomeIds::ROOFED_FOREST_MUTATED
		);

		self::register(new BiomeClimate(0.6, 0.6, true),
			BiomeIds::BIRCH_FOREST,
			BiomeIds::BIRCH_FOREST_HILLS,
			BiomeIds::BIRCH_FOREST_MUTATED,
			BiomeIds::BIRCH_FOREST_HILLS_MUTATED
		);

		self::register(new BiomeClimate(0.25, 0.8, true),
			BiomeIds::TAIGA,
			BiomeIds::TAIGA_HILLS,
			BiomeIds::TAIGA_MUTATED,
			BiomeIds::REDWOOD_TAIGA_MUTATED,
			BiomeIds::REDWOOD_TAIGA_HILLS_MUTATED
		);

		self::register(new BiomeClimate(0.8, 0.9, true),
			BiomeIds::SWAMPLAND,
			BiomeIds::SWAMPLAND_MUTATED
		);

		self::register(new BiomeClimate(0.0, 0.5, true),
			BiomeIds::ICE_PLAINS,
			BiomeIds::ICE_MOUNTAINS,
			BiomeIds::ICE_PLAINS_SPIKES,
			BiomeIds::FROZEN_RIVER,
			BiomeIds::FROZEN_OCEAN
		);

		self::register(new BiomeClimate(0.9, 1.0, true), BiomeIds::MUSHROOM_ISLAND, BiomeIds::MUSHROOM_ISLAND_SHORE);
		self::register(new BiomeClimate(0.05, 0.3, true), BiomeIds::COLD_BEACH);
		self::register(new BiomeClimate(0.95, 0.9, true), BiomeIds::JUNGLE_HILLS, BiomeIds::JUNGLE_MUTATED);
		self::register(new BiomeClimate(0.95, 0.8, true), BiomeIds::JUNGLE_EDGE, BiomeIds::JUNGLE_EDGE_MUTATED);
		self::register(new BiomeClimate(-0.5, 0.4, true), BiomeIds::COLD_TAIGA, BiomeIds::COLD_TAIGA_HILLS, BiomeIds::COLD_TAIGA_MUTATED);
		self::register(new BiomeClimate(0.3, 0.8, true), BiomeIds::MEGA_TAIGA, BiomeIds::MEGA_TAIGA_HILLS);
		self::register(new BiomeClimate(1.2, 0.0, false), BiomeIds::SAVANNA);
		self::register(new BiomeClimate(1.1, 0.0, false), BiomeIds::SAVANNA_MUTATED);
		self::register(new BiomeClimate(1.0, 0.0, false), BiomeIds::SAVANNA_PLATEAU);
		self::register(new BiomeClimate(0.5, 0.0, false), BiomeIds::SAVANNA_PLATEAU_MUTATED);
		self::register(new BiomeClimate(0.5, 0.5, false), BiomeIds::SKY);
	}

	public static function register(BiomeClimate $climate, int ...$biome_ids) : void{
		foreach($biome_ids as $biomeId){
			self::$climates[$biomeId] = $climate;
		}
	}

	public static function get(int $biome) : BiomeClimate{
		return self::$climates[$biome] ?? self::$default;
	}

	public static function getBiomeTemperature(int $biome) : float{
		return self::get($biome)->temperature;
	}

	public static function getBiomeHumidity(int $biome) : float{
		return self::get($biome)->humidity;
	}

	public static function isWet(int $biome) : bool{
		return self::getBiomeHumidity($biome) > 0.85;
	}

	public static function isCold(int $biome, int $x, int $y, int $z) : bool{
		return self::getVariatedTemperature($biome, $x, $y, $z) < 0.15;
	}

	public static function isRainy(int $biome, int $x, int $y, int $z) : bool{
		return self::get($biome)->rainy && !self::isCold($biome, $x, $y, $z);
	}

	public static function isSnowy(int $biome, int $x, int $y, int $z) : bool{
		return self::get($biome)->rainy && self::isCold($biome, $x, $y, $z);
	}

	private static function getVariatedTemperature(int $biome, int $x, int $y, int $z) : float{
		$temp = self::get($biome)->temperature;
		if($y > 64){
			$variation = self::$noise_gen->noise($x, $z, 0, 0.5, 2.0, false) * 4.0;
			return $temp - ($variation + (float) ($y - 64)) * 0.05 / 30.0;
		}

		return $temp;
	}
}

BiomeClimateManager::init();