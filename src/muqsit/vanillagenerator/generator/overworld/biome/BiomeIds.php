<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

use pocketmine\data\bedrock\BiomeIds as VanillaBiomeIds;

interface BiomeIds{

	public const OCEAN = VanillaBiomeIds::OCEAN;
	public const PLAINS = VanillaBiomeIds::PLAINS;
	public const DESERT = VanillaBiomeIds::DESERT;
	public const EXTREME_HILLS = VanillaBiomeIds::EXTREME_HILLS;
	public const FOREST = VanillaBiomeIds::FOREST;
	public const TAIGA = VanillaBiomeIds::TAIGA;
	public const SWAMPLAND = VanillaBiomeIds::SWAMPLAND;
	public const RIVER = VanillaBiomeIds::RIVER;
	public const HELL = VanillaBiomeIds::HELL;
	public const SKY = 9;
	public const FROZEN_OCEAN = VanillaBiomeIds::LEGACY_FROZEN_OCEAN;
	public const FROZEN_RIVER = VanillaBiomeIds::FROZEN_RIVER;
	public const ICE_PLAINS = VanillaBiomeIds::ICE_PLAINS;
	public const ICE_MOUNTAINS = VanillaBiomeIds::ICE_MOUNTAINS;
	public const MUSHROOM_ISLAND = VanillaBiomeIds::MUSHROOM_ISLAND;
	public const MUSHROOM_ISLAND_SHORE = VanillaBiomeIds::MUSHROOM_ISLAND_SHORE;
	public const BEACH = VanillaBiomeIds::BEACH;
	public const DESERT_HILLS = VanillaBiomeIds::DESERT_HILLS;
	public const FOREST_HILLS = VanillaBiomeIds::FOREST_HILLS;
	public const TAIGA_HILLS = VanillaBiomeIds::TAIGA_HILLS;
	public const EXTREME_HILLS_EDGE = VanillaBiomeIds::EXTREME_HILLS_EDGE;
	public const JUNGLE = VanillaBiomeIds::JUNGLE;
	public const JUNGLE_HILLS = VanillaBiomeIds::JUNGLE_HILLS;
	public const JUNGLE_EDGE = VanillaBiomeIds::JUNGLE_EDGE;
	public const DEEP_OCEAN = VanillaBiomeIds::DEEP_OCEAN;
	public const STONE_BEACH = VanillaBiomeIds::STONE_BEACH;
	public const COLD_BEACH = VanillaBiomeIds::COLD_BEACH;
	public const BIRCH_FOREST = VanillaBiomeIds::BIRCH_FOREST;
	public const BIRCH_FOREST_HILLS = VanillaBiomeIds::BIRCH_FOREST_HILLS;
	public const ROOFED_FOREST = VanillaBiomeIds::ROOFED_FOREST;
	public const COLD_TAIGA = VanillaBiomeIds::COLD_TAIGA;
	public const COLD_TAIGA_HILLS = VanillaBiomeIds::COLD_TAIGA_HILLS;
	public const MEGA_TAIGA = VanillaBiomeIds::MEGA_TAIGA;
	public const MEGA_TAIGA_HILLS = VanillaBiomeIds::MEGA_TAIGA_HILLS;
	public const EXTREME_HILLS_PLUS_TREES = VanillaBiomeIds::EXTREME_HILLS_PLUS_TREES;
	public const SAVANNA = VanillaBiomeIds::SAVANNA;
	public const SAVANNA_PLATEAU = VanillaBiomeIds::SAVANNA_PLATEAU;
	public const MESA = VanillaBiomeIds::MESA;
	public const MESA_PLATEAU_STONE = VanillaBiomeIds::MESA_PLATEAU_STONE;
	public const MESA_PLATEAU = VanillaBiomeIds::MESA_PLATEAU;
	public const SUNFLOWER_PLAINS = VanillaBiomeIds::SUNFLOWER_PLAINS;
	public const DESERT_MUTATED = VanillaBiomeIds::DESERT_MUTATED;
	public const EXTREME_HILLS_MUTATED = VanillaBiomeIds::EXTREME_HILLS_MUTATED;
	public const FLOWER_FOREST = VanillaBiomeIds::FLOWER_FOREST;
	public const TAIGA_MUTATED = VanillaBiomeIds::TAIGA_MUTATED;
	public const SWAMPLAND_MUTATED = VanillaBiomeIds::SWAMPLAND_MUTATED;
	public const ICE_PLAINS_SPIKES = VanillaBiomeIds::ICE_PLAINS_SPIKES;
	public const JUNGLE_MUTATED = VanillaBiomeIds::JUNGLE_MUTATED;
	public const JUNGLE_EDGE_MUTATED = VanillaBiomeIds::JUNGLE_EDGE_MUTATED;
	public const BIRCH_FOREST_MUTATED = VanillaBiomeIds::BIRCH_FOREST_MUTATED;
	public const BIRCH_FOREST_HILLS_MUTATED = VanillaBiomeIds::BIRCH_FOREST_HILLS_MUTATED;
	public const ROOFED_FOREST_MUTATED = VanillaBiomeIds::ROOFED_FOREST_MUTATED;
	public const COLD_TAIGA_MUTATED = VanillaBiomeIds::COLD_TAIGA_MUTATED;
	public const REDWOOD_TAIGA_MUTATED = VanillaBiomeIds::REDWOOD_TAIGA_MUTATED;
	public const REDWOOD_TAIGA_HILLS_MUTATED = VanillaBiomeIds::REDWOOD_TAIGA_HILLS_MUTATED;
	public const EXTREME_HILLS_PLUS_TREES_MUTATED = VanillaBiomeIds::EXTREME_HILLS_PLUS_TREES_MUTATED;
	public const SAVANNA_MUTATED = VanillaBiomeIds::SAVANNA_MUTATED;
	public const SAVANNA_PLATEAU_MUTATED = VanillaBiomeIds::SAVANNA_PLATEAU_MUTATED;
	public const MESA_BRYCE = VanillaBiomeIds::MESA_BRYCE;
	public const MESA_PLATEAU_STONE_MUTATED = VanillaBiomeIds::MESA_PLATEAU_STONE_MUTATED;
	public const MESA_PLATEAU_MUTATED = VanillaBiomeIds::MESA_PLATEAU_MUTATED;
	
	// 1.18+ Underground Biomes
	public const DEEP_DARK = VanillaBiomeIds::DEEP_DARK; // Deep Dark biome for Ancient Cities
	public const DRIPSTONE_CAVES = VanillaBiomeIds::DRIPSTONE_CAVES; // Dripstone cave biome
	public const LUSH_CAVES = VanillaBiomeIds::LUSH_CAVES; // Lush cave biome
	
	// 1.18+ Surface Biomes  
	public const MANGROVE_SWAMP = VanillaBiomeIds::MANGROVE_SWAMP; // Mangrove swamp biome
	public const MEADOW = VanillaBiomeIds::MEADOW; // Mountain meadow biome
	public const GROVE = VanillaBiomeIds::GROVE; // Grove biome (snowy)
	public const SNOWY_SLOPES = VanillaBiomeIds::SNOWY_SLOPES; // Snowy mountain slopes
	public const FROZEN_PEAKS = VanillaBiomeIds::FROZEN_PEAKS; // Frozen mountain peaks
	public const JAGGED_PEAKS = VanillaBiomeIds::JAGGED_PEAKS; // Jagged mountain peaks
	public const STONY_PEAKS = VanillaBiomeIds::STONY_PEAKS; // Stony mountain peaks
}