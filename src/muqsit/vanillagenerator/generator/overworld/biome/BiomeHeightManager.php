<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

final class BiomeHeightManager{

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
		self::register(new BiomeHeight(0.125, 0.05), BiomeIds::DESERT, BiomeIds::ICE_PLAINS, BiomeIds::SAVANNA);

		self::register(new BiomeHeight(1.0, 0.5),
			BiomeIds::EXTREME_HILLS,
			BiomeIds::EXTREME_HILLS_PLUS_TREES,
			BiomeIds::EXTREME_HILLS_MUTATED,
			BiomeIds::EXTREME_HILLS_PLUS_TREES_MUTATED
		);

		self::register(new BiomeHeight(0.2, 0.2), BiomeIds::TAIGA, BiomeIds::COLD_TAIGA, BiomeIds::MEGA_TAIGA);
		self::register(new BiomeHeight(-0.2, 0.1), BiomeIds::SWAMPLAND);
		self::register(new BiomeHeight(0.2, 0.3), BiomeIds::MUSHROOM_ISLAND);

		self::register(new BiomeHeight(0.45, 0.3),
			BiomeIds::ICE_MOUNTAINS,
			BiomeIds::DESERT_HILLS,
			BiomeIds::FOREST_HILLS,
			BiomeIds::TAIGA_HILLS,
			BiomeIds::EXTREME_HILLS_EDGE,
			BiomeIds::JUNGLE_HILLS,
			BiomeIds::BIRCH_FOREST_HILLS,
			BiomeIds::COLD_TAIGA_HILLS,
			BiomeIds::MEGA_TAIGA_HILLS,
			BiomeIds::MESA_PLATEAU_STONE_MUTATED,
			BiomeIds::MESA_PLATEAU_MUTATED
		);

		self::register(new BiomeHeight(1.5, 0.025), BiomeIds::SAVANNA_PLATEAU, BiomeIds::MESA_PLATEAU_STONE, BiomeIds::MESA_PLATEAU);
		self::register(new BiomeHeight(0.275, 0.25), BiomeIds::DESERT_MUTATED);
		self::register(new BiomeHeight(0.525, 0.55), BiomeIds::ICE_PLAINS_SPIKES);
		self::register(new BiomeHeight(0.55, 0.5), BiomeIds::BIRCH_FOREST_HILLS_MUTATED);
		self::register(new BiomeHeight(-0.1, 0.3), BiomeIds::SWAMPLAND_MUTATED);
		self::register(new BiomeHeight(0.2, 0.4), BiomeIds::JUNGLE_MUTATED, BiomeIds::JUNGLE_EDGE_MUTATED, BiomeIds::BIRCH_FOREST_MUTATED, BiomeIds::ROOFED_FOREST_MUTATED);
		self::register(new BiomeHeight(0.3, 0.4), BiomeIds::TAIGA_MUTATED, BiomeIds::COLD_TAIGA_MUTATED, BiomeIds::REDWOOD_TAIGA_MUTATED, BiomeIds::REDWOOD_TAIGA_HILLS_MUTATED);
		self::register(new BiomeHeight(0.1, 0.4), BiomeIds::FLOWER_FOREST);
		self::register(new BiomeHeight(0.4125, 1.325), BiomeIds::SAVANNA_MUTATED);
		self::register(new BiomeHeight(1.1, 1.3125), BiomeIds::SAVANNA_PLATEAU_MUTATED);
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