<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\DoublePlant;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class DoubleTallPlant extends TerrainObject{

	/** @var DoublePlant */
	private $species;

	public function __construct(DoublePlant $species){
		$this->species = $species;
	}

	/**
	 * Generates up to 64 plants around the given point.
	 *
	 * @param ChunkManager $world
	 * @param Random $random
	 * @param int $sourceX
	 * @param int $sourceY
	 * @param int $sourceZ
	 * @return bool true whether least one plant was successfully generated
	 */
	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$placed = false;
		$height = $world->getWorldHeight();
		$species_top = BlockFactory::getInstance()->get($this->species->getId(), BlockLegacyMetadata::DOUBLE_PLANT_FLAG_TOP);
		for($i = 0; $i < 64; ++$i){
			$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			$block = $world->getBlockAt($x, $y, $z);
			$topBlock = $world->getBlockAt($x, $y + 1, $z);
			if($y < $height && $block->getId() === BlockLegacyIds::AIR && $topBlock->getId() === BlockLegacyIds::AIR && $world->getBlockAt($x, $y - 1, $z)->getId() === BlockLegacyIds::GRASS){
				$world->setBlockAt($x, $y, $z, $this->species);
				$world->setBlockAt($x, $y + 1, $z, $species_top);
				$placed = true;
			}
		}

		return $placed;
	}
}