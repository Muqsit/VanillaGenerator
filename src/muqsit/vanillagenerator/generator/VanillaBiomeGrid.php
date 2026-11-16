<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use muqsit\vanillagenerator\generator\biomegrid\BiomeGrid;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;

class VanillaBiomeGrid implements BiomeGrid{

	public PalettedBlockArray $palette;

	public function __construct(){
		$this->palette = new PalettedBlockArray(BiomeIds::OCEAN);
	}

	public function getBiome(int $x, int $z) : int{
		return $this->palette->get($x, 0, $z);
	}

	public function setBiome(int $x, int $z, int $biome_id) : void{
		for($y = 0; $y < SubChunk::EDGE_LENGTH; ++$y){
			$this->palette->set($x, $y, $z, $biome_id);
		}
	}
}