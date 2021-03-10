<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\utils\Random;

final class BiomeClimateManager{

	/** @var SimplexOctaveGenerator */
	private static SimplexOctaveGenerator $noise_gen;

	/** @var BiomeClimate */
	private static BiomeClimate $default;

	/** @var BiomeClimate[] */
	private static array $climates = [];

	public static function init() : void{
		self::$noise_gen = SimplexOctaveGenerator::fromRandomAndOctaves(new Random(1234), 1, 0, 0, 0);
		self::$noise_gen->setScale(1 / 8.0);

		self::$default = new BiomeClimate(0.5, 0.5, true);

		self::register(new BiomeClimate(0.8, 0.4, true),
			BiomeIds::PLAINS,
			BiomeIds::MUTATED_PLAINS,
			BiomeIds::BEACH
		);

		self::register(new BiomeClimate(2.0, 0.0, false),
			BiomeIds::DESERT,
			BiomeIds::DESERT_HILLS,
			BiomeIds::MUTATED_DESERT,
			BiomeIds::MESA,
			BiomeIds::MUTATED_MESA,
			BiomeIds::MESA_CLEAR_ROCK,
			BiomeIds::MESA_ROCK,
			BiomeIds::MUTATED_MESA_CLEAR_ROCK,
			BiomeIds::MUTATED_MESA_ROCK,
			BiomeIds::HELL
		);

		self::register(new BiomeClimate(0.2, 0.3, true),
			BiomeIds::EXTREME_HILLS,
			BiomeIds::EXTREME_HILLS_WITH_TREES,
			BiomeIds::MUTATED_EXTREME_HILLS,
			BiomeIds::MUTATED_EXTREME_HILLS_WITH_TREES,
			BiomeIds::STONE_BEACH,
			BiomeIds::SMALLER_EXTREME_HILLS
		);

		self::register(new BiomeClimate(0.7, 0.8, true),
			BiomeIds::FOREST,
			BiomeIds::FOREST_HILLS,
			BiomeIds::MUTATED_FOREST,
			BiomeIds::ROOFED_FOREST,
			BiomeIds::MUTATED_ROOFED_FOREST
		);

		self::register(new BiomeClimate(0.6, 0.6, true),
			BiomeIds::BIRCH_FOREST,
			BiomeIds::BIRCH_FOREST_HILLS,
			BiomeIds::MUTATED_BIRCH_FOREST,
			BiomeIds::MUTATED_BIRCH_FOREST_HILLS
		);

		self::register(new BiomeClimate(0.25, 0.8, true),
			BiomeIds::TAIGA,
			BiomeIds::TAIGA_HILLS,
			BiomeIds::MUTATED_TAIGA,
			BiomeIds::MUTATED_REDWOOD_TAIGA,
			BiomeIds::MUTATED_REDWOOD_TAIGA_HILLS
		);

		self::register(new BiomeClimate(0.8, 0.9, true),
			BiomeIds::SWAMPLAND,
			BiomeIds::MUTATED_SWAMPLAND
		);

		self::register(new BiomeClimate(0.0, 0.5, true),
			BiomeIds::ICE_FLATS,
			BiomeIds::ICE_MOUNTAINS,
			BiomeIds::MUTATED_ICE_FLATS,
			BiomeIds::FROZEN_RIVER,
			BiomeIds::FROZEN_OCEAN
		);

		self::register(new BiomeClimate(0.9, 1.0, true), BiomeIds::MUSHROOM_ISLAND, BiomeIds::MUSHROOM_ISLAND_SHORE);
		self::register(new BiomeClimate(0.05, 0.3, true), BiomeIds::COLD_BEACH);
		self::register(new BiomeClimate(0.95, 0.9, true), BiomeIds::JUNGLE_HILLS, BiomeIds::MUTATED_JUNGLE);
		self::register(new BiomeClimate(0.95, 0.8, true), BiomeIds::JUNGLE_EDGE, BiomeIds::MUTATED_JUNGLE_EDGE);
		self::register(new BiomeClimate(-0.5, 0.4, true), BiomeIds::TAIGA_COLD, BiomeIds::TAIGA_COLD_HILLS, BiomeIds::MUTATED_TAIGA_COLD);
		self::register(new BiomeClimate(0.3, 0.8, true), BiomeIds::REDWOOD_TAIGA, BiomeIds::REDWOOD_TAIGA_HILLS);
		self::register(new BiomeClimate(1.2, 0.0, false), BiomeIds::SAVANNA);
		self::register(new BiomeClimate(1.1, 0.0, false), BiomeIds::MUTATED_SAVANNA);
		self::register(new BiomeClimate(1.0, 0.0, false), BiomeIds::SAVANNA_ROCK);
		self::register(new BiomeClimate(0.5, 0.0, false), BiomeIds::MUTATED_SAVANNA_ROCK);
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
		return self::get($biome)->getTemperature();
	}

	public static function getBiomeHumidity(int $biome) : float{
		return self::get($biome)->getHumidity();
	}

	public static function isWet(int $biome) : bool{
		return self::getBiomeHumidity($biome) > 0.85;
	}

	public static function isCold(int $biome, int $x, int $y, int $z) : bool{
		return self::getVariatedTemperature($biome, $x, $y, $z) < 0.15;
	}

	public static function isRainy(int $biome, int $x, int $y, int $z) : bool{
		return self::get($biome)->isRainy() && !self::isCold($biome, $x, $y, $z);
	}

	public static function isSnowy(int $biome, int $x, int $y, int $z) : bool{
		return self::get($biome)->isRainy() && self::isCold($biome, $x, $y, $z);
	}

	private static function getVariatedTemperature(int $biome, int $x, int $y, int $z) : float{
		$temp = self::get($biome)->getTemperature();
		if($y > 64){
			$variation = self::$noise_gen->noise($x, $z, 0, 0.5, 2.0, false) * 4.0;
			return $temp - ($variation + (float) ($y - 64)) * 0.05 / 30.0;
		}

		return $temp;
	}
}

BiomeClimateManager::init();