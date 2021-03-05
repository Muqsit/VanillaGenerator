<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid;

interface BiomeGrid{

	/**
	 * Get biome at x, z within chunk being generated
	 *
	 * @param int $x - 0-15
	 * @param int $z - 0-15
	 * @return int|null
	 */
	public function getBiome(int $x, int $z) : ?int;

	/**
	 * Set biome at x, z within chunk being generated
	 *
	 * @param int $x - 0-15
	 * @param int $z - 0-15
	 * @param int $biome_id
	 */
	public function setBiome(int $x, int $z, int $biome_id) : void;
}