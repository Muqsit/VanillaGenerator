<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use muqsit\vanillagenerator\generator\biomegrid\BiomeGrid;
use pocketmine\world\format\Chunk;
use function array_key_exists;

class VanillaBiomeGrid implements BiomeGrid{

	/** @var int[] */
	public array $biomes = [];
	
	/** @var int[] */
	private array $biomes_3d = [];

	public function getBiome(int $x, int $z) : ?int{
		// upcasting is very important to get extended biomes
		return array_key_exists($hash = $x | $z << Chunk::COORD_BIT_SIZE, $this->biomes) ? $this->biomes[$hash] & 0xFF : null;
	}

	public function getBiome3D(int $x, int $y, int $z) : ?int{
		// For 3D biomes, we use a different hash that includes Y coordinate
		// We sample biomes every 4 blocks in Y (like 1.18+) to save memory
		$sample_y = $y >> 2; // Sample every 4 blocks in Y direction
		$hash = ($x) | ($z << 4) | ($sample_y << 8);
		
		if(array_key_exists($hash, $this->biomes_3d)) {
			return $this->biomes_3d[$hash] & 0xFF;
		}
		
		// Fall back to surface biome if no 3D biome is set
		return $this->getBiome($x, $z);
	}

	public function setBiome(int $x, int $z, int $biome_id) : void{
		$this->biomes[$x | $z << Chunk::COORD_BIT_SIZE] = $biome_id;
	}

	public function setBiome3D(int $x, int $y, int $z, int $biome_id) : void{
		// Sample biomes every 4 blocks in Y (like 1.18+)
		$sample_y = $y >> 2;
		$hash = ($x) | ($z << 4) | ($sample_y << 8);
		$this->biomes_3d[$hash] = $biome_id;
	}
}