<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use muqsit\vanillagenerator\generator\biomegrid\BiomeGrid;
use pocketmine\world\format\Chunk;
use function array_key_exists;

class VanillaBiomeGrid implements BiomeGrid{

	/** @var int[] */
	public array $biomes = [];

	public function getBiome(int $x, int $z) : ?int{
		// upcasting is very important to get extended biomes
		return array_key_exists($hash = $x | $z << Chunk::COORD_BIT_SIZE, $this->biomes) ? $this->biomes[$hash] & 0xFF : null;
	}

	public function setBiome(int $x, int $z, int $biome_id) : void{
		$this->biomes[$x | $z << Chunk::COORD_BIT_SIZE] = $biome_id;
	}
}