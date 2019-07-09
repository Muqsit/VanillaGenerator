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

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = ($chunk->getX() << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunk->getZ() << 4) + $random->nextBoundedInt(16);
		$topBlock = $chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f);
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
		(new TallGrass(BlockFactory::get(BlockLegacyIds::TALL_GRASS, $species)))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
	}
}