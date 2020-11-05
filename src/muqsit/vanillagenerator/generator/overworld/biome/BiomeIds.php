<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\biome;

use pocketmine\data\bedrock\BiomeIds as VanillaBiomeIds;

interface BiomeIds{

	public const OCEAN = VanillaBiomeIds::OCEAN;
	public const PLAINS = VanillaBiomeIds::PLAINS;
	public const DESERT = VanillaBiomeIds::DESERT;
	public const EXTREME_HILLS = VanillaBiomeIds::MOUNTAINS;
	public const FOREST = VanillaBiomeIds::FOREST;
	public const TAIGA = VanillaBiomeIds::TAIGA;
	public const SWAMPLAND = VanillaBiomeIds::SWAMP;
	public const RIVER = VanillaBiomeIds::RIVER;
	public const HELL = VanillaBiomeIds::HELL;
	public const SKY = 9;
	public const FROZEN_OCEAN = 10;
	public const FROZEN_RIVER = 11;
	public const ICE_PLAINS = VanillaBiomeIds::ICE_PLAINS, ICE_FLATS = VanillaBiomeIds::ICE_PLAINS;
	public const ICE_MOUNTAINS = 13;
	public const MUSHROOM_ISLAND = 14;
	public const MUSHROOM_SHORE = 15, MUSHROOM_ISLAND_SHORE = 15;
	public const BEACH = 16;
	public const DESERT_HILLS = 17;
	public const FOREST_HILLS = 18;
	public const TAIGA_HILLS = 19;
	public const SMALL_MOUNTAINS = VanillaBiomeIds::SMALL_MOUNTAINS, SMALLER_EXTREME_HILLS = VanillaBiomeIds::SMALL_MOUNTAINS; // EXTREME_HILLS_EDGE
	public const JUNGLE = 21;
	public const JUNGLE_HILLS = 22;
	public const JUNGLE_EDGE = 23;
	public const DEEP_OCEAN = 24;
	public const STONE_BEACH = 25;
	public const COLD_BEACH = 26;
	public const BIRCH_FOREST = VanillaBiomeIds::BIRCH_FOREST;
	public const BIRCH_FOREST_HILLS = 28;
	public const ROOFED_FOREST = 29;
	public const COLD_TAIGA = 30, TAIGA_COLD = 30;
	public const COLD_TAIGA_HILLS = 31, TAIGA_COLD_HILLS = 31;
	public const MEGA_TAIGA = 32, REDWOOD_TAIGA = 32;
	public const MEGA_TAIGA_HILLS = 33, REDWOOD_TAIGA_HILLS = 33;
	public const EXTREME_HILLS_PLUS = 34, EXTREME_HILLS_WITH_TREES = 34;
	public const SAVANNA = 35;
	public const SAVANNA_PLATEAU = 36, SAVANNA_ROCK = 36;
	public const MESA = 37;
	public const MESA_PLATEAU_FOREST = 38, MESA_ROCK = 38;
	public const MESA_PLATEAU = 39, MESA_CLEAR_ROCK = 39;

	public const SUNFLOWER_PLAINS = 129, MUTATED_PLAINS = 129;
	public const DESERT_MOUNTAINS = 130, MUTATED_DESERT = 130;
	public const EXTREME_HILLS_MOUNTAINS = 131, MUTATED_EXTREME_HILLS = 131;
	public const FLOWER_FOREST = 132, MUTATED_FOREST = 132;
	public const TAIGA_MOUNTAINS = 133, MUTATED_TAIGA = 133;
	public const SWAMPLAND_MOUNTAINS = 134, MUTATED_SWAMPLAND = 134;

	public const ICE_PLAINS_SPIKES = 140, MUTATED_ICE_FLATS = 140;

	public const JUNGLE_MOUNTAINS = 149, MUTATED_JUNGLE = 149;

	public const JUNGLE_EDGE_MOUNTAINS = 151, MUTATED_JUNGLE_EDGE = 151;

	public const BIRCH_FOREST_MOUNTAINS = 155, MUTATED_BIRCH_FOREST = 155;
	public const BIRCH_FOREST_HILLS_MOUNTAINS = 156, MUTATED_BIRCH_FOREST_HILLS = 156;
	public const ROOFED_FOREST_MOUNTAINS = 157, MUTATED_ROOFED_FOREST = 157;
	public const COLD_TAIGA_MOUNTAINS = 158, MUTATED_TAIGA_COLD = 158;

	public const MEGA_SPRUCE_TAIGA = 160, MUTATED_REDWOOD_TAIGA = 160;
	public const MEGA_SPRUCE_TAIGA_HILLS = 161, MUTATED_REDWOOD_TAIGA_HILLS = 161;
	public const EXTREME_HILLS_PLUS_MOUNTAINS = 162, MUTATED_EXTREME_HILLS_WITH_TREES = 162;
	public const SAVANNA_MOUNTAINS = 163, MUTATED_SAVANNA = 163;
	public const SAVANNA_PLATEAU_MOUNTAINS = 164, MUTATED_SAVANNA_ROCK = 164;
	public const MESA_BRYCE = 165, MUTATED_MESA = 165;
	public const MESA_PLATEAU_FOREST_MOUNTAINS = 166, MUTATED_MESA_ROCK = 166;
	public const MESA_PLATEAU_MOUNTAINS = 167, MUTATED_MESA_CLEAR_ROCK = 167;
}