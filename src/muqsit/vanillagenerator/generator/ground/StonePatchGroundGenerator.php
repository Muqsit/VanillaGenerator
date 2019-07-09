<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class StonePatchGroundGenerator extends GroundGenerator{

	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surfaceNoise) : void{
		if($surfaceNoise > 1.0){
			$this->setTopMaterial(self::$STONE);
			$this->setGroundMaterial(self::$STONE);
		}else{
			$this->setTopMaterial(self::$GRASS);
			$this->setGroundMaterial(self::$DIRT);
		}

		parent::generateTerrainColumn($world, $random, $x, $z, $biome, $surfaceNoise);
	}
}