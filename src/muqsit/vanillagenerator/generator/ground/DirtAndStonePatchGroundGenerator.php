<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class DirtAndStonePatchGroundGenerator extends GroundGenerator{

	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surface_noise) : void{
		[$top, $ground] = match(true){
			$surface_noise > 1.75 => [VanillaBlocks::STONE(), VanillaBlocks::STONE()],
			$surface_noise > -0.5 => [VanillaBlocks::DIRT()->setDirtType(DirtType::COARSE), VanillaBlocks::DIRT()],
			default => [VanillaBlocks::GRASS(), VanillaBlocks::DIRT()]
		};
		$this->setTopMaterial($top);
		$this->setGroundMaterial($ground);
		parent::generateTerrainColumn($world, $random, $x, $z, $biome, $surface_noise);
	}
}