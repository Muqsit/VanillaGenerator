<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class DirtPatchGroundGenerator extends GroundGenerator{

	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surfaceNoise) : void{
		if($surfaceNoise > 1.75){
			$this->setTopMaterial(self::$COARSE_DIRT);
		}elseif($surfaceNoise > -0.95){
			$this->setTopMaterial(self::$PODZOL);
		}else{
			$this->setTopMaterial(self::$GRASS);
		}
		$this->setGroundMaterial(self::$DIRT);

		parent::generateTerrainColumn($world, $random, $x, $z, $biome, $surfaceNoise);
	}
}