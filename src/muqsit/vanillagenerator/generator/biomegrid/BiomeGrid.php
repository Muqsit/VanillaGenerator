<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

interface BiomeGrid{

	/**
	 * Get biome at x, z within chunk being generated (2D surface biome)
	 *
	 * @param int $x - 0-15
	 * @param int $z - 0-15
	 * @return int|null
	 */
	public function getBiome(int $x, int $z) : ?int;

	/**
	 * Get biome at x, y, z within chunk being generated (3D biome support for 1.18+)
	 *
	 * @param int $x - 0-15
	 * @param int $y - world min to world max
	 * @param int $z - 0-15
	 * @return int|null
	 */
	public function getBiome3D(int $x, int $y, int $z) : ?int;

	/**
	 * Set biome at x, z within chunk being generated (2D surface biome)
	 *
	 * @param int $x - 0-15
	 * @param int $z - 0-15
	 * @param int $biome_id
	 */
	public function setBiome(int $x, int $z, int $biome_id) : void;

	/**
	 * Set biome at x, y, z within chunk being generated (3D biome support for 1.18+)
	 *
	 * @param int $x - 0-15
	 * @param int $y - world min to world max
	 * @param int $z - 0-15
	 * @param int $biome_id
	 */
	public function setBiome3D(int $x, int $y, int $z, int $biome_id) : void;
}