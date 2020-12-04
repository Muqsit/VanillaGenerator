<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\TallGrass;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class TallGrassDecorator extends Decorator{

	/** @var float */
	private $fernDensity = 0.0;

	final public function setFernDensity(float $fernDensity) : void{
		$this->fernDensity = $fernDensity;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$topBlock = $chunk->getHighestBlockAt($x, $z);
		if($topBlock <= 0){
			// Nothing to do if this column is empty
			return;
		}

		$sourceY = $random->nextBoundedInt(abs($topBlock << 1));

		// the grass species can change on each decoration pass
		$species = BlockLegacyMetadata::TALLGRASS_NORMAL;
		if($this->fernDensity > 0 && $random->nextFloat() < $this->fernDensity){
			$species = BlockLegacyMetadata::TALLGRASS_FERN;
		}
		(new TallGrass(BlockFactory::getInstance()->get(BlockLegacyIds::TALL_GRASS, $species)))->generate($world, $random, ($chunkX << 4) + $x, $sourceY, ($chunkZ << 4) + $z);
	}
}