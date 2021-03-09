<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

final class BiomeHeightManager{

	/** @var BiomeHeight */
	private static BiomeHeight $default;

	/** @var BiomeHeight[] */
	private static array $heights = [];

	public static function init() : void{
		self::$default = new BiomeHeight(0.1, 0.2);

		self::register(new BiomeHeight(-1.0, 0.1), BiomeIds::OCEAN, BiomeIds::FROZEN_OCEAN);
		self::register(new BiomeHeight(-1.8, 0.1), BiomeIds::DEEP_OCEAN);
		self::register(new BiomeHeight(-0.5, 0.0), BiomeIds::RIVER, BiomeIds::FROZEN_RIVER);
		self::register(new BiomeHeight(0.0, 0.025), BiomeIds::BEACH, BiomeIds::COLD_BEACH, BiomeIds::MUSHROOM_ISLAND_SHORE);
		self::register(new BiomeHeight(0.1, 0.8), BiomeIds::STONE_BEACH);
		self::register(new BiomeHeight(0.125, 0.05), BiomeIds::DESERT, BiomeIds::ICE_FLATS, BiomeIds::SAVANNA);

		self::register(new BiomeHeight(1.0, 0.5),
			BiomeIds::EXTREME_HILLS,
			BiomeIds::EXTREME_HILLS_WITH_TREES,
			BiomeIds::MUTATED_EXTREME_HILLS,
			BiomeIds::MUTATED_EXTREME_HILLS_WITH_TREES
		);

		self::register(new BiomeHeight(0.2, 0.2), BiomeIds::TAIGA, BiomeIds::TAIGA_COLD, BiomeIds::REDWOOD_TAIGA);
		self::register(new BiomeHeight(-0.2, 0.1), BiomeIds::SWAMPLAND);
		self::register(new BiomeHeight(0.2, 0.3), BiomeIds::MUSHROOM_ISLAND);

		self::register(new BiomeHeight(0.45, 0.3),
			BiomeIds::ICE_MOUNTAINS,
			BiomeIds::DESERT_HILLS,
			BiomeIds::FOREST_HILLS,
			BiomeIds::TAIGA_HILLS,
			BiomeIds::SMALLER_EXTREME_HILLS,
			BiomeIds::JUNGLE_HILLS,
			BiomeIds::BIRCH_FOREST_HILLS,
			BiomeIds::TAIGA_COLD_HILLS,
			BiomeIds::REDWOOD_TAIGA_HILLS,
			BiomeIds::MUTATED_MESA_ROCK,
			BiomeIds::MUTATED_MESA_CLEAR_ROCK
		);

		self::register(new BiomeHeight(1.5, 0.025), BiomeIds::SAVANNA_ROCK, BiomeIds::MESA_ROCK, BiomeIds::MESA_CLEAR_ROCK);
		self::register(new BiomeHeight(0.275, 0.25), BiomeIds::MUTATED_DESERT);
		self::register(new BiomeHeight(0.525, 0.55), BiomeIds::MUTATED_ICE_FLATS);
		self::register(new BiomeHeight(0.55, 0.5), BiomeIds::MUTATED_BIRCH_FOREST_HILLS);
		self::register(new BiomeHeight(-0.1, 0.3), BiomeIds::MUTATED_SWAMPLAND);
		self::register(new BiomeHeight(0.2, 0.4), BiomeIds::MUTATED_JUNGLE, BiomeIds::MUTATED_JUNGLE_EDGE, BiomeIds::MUTATED_BIRCH_FOREST, BiomeIds::MUTATED_ROOFED_FOREST);
		self::register(new BiomeHeight(0.3, 0.4), BiomeIds::MUTATED_TAIGA, BiomeIds::MUTATED_TAIGA_COLD, BiomeIds::MUTATED_REDWOOD_TAIGA, BiomeIds::MUTATED_REDWOOD_TAIGA_HILLS);
		self::register(new BiomeHeight(0.1, 0.4), BiomeIds::MUTATED_FOREST);
		self::register(new BiomeHeight(0.4125, 1.325), BiomeIds::MUTATED_SAVANNA);
		self::register(new BiomeHeight(1.1, 1.3125), BiomeIds::MUTATED_SAVANNA_ROCK);
	}

	public static function register(BiomeHeight $height, int ...$biomes) : void{
		foreach($biomes as $biome){
			self::$heights[$biome] = $height;
		}
	}

	public static function get(int $biome) : BiomeHeight{
		return self::$heights[$biome] ?? self::$default;
	}
}

BiomeHeightManager::init();