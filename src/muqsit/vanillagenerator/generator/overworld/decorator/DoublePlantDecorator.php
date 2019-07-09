<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\DoubleTallPlant;
use muqsit\vanillagenerator\generator\overworld\decorator\types\DoublePlantDecoration;
use pocketmine\block\Block;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class DoublePlantDecorator extends Decorator{

	/**
	 * @param Random $random
	 * @param DoublePlantDecoration[] $decorations
	 * @return Block|null
	 */
	private static function getRandomDoublePlant(Random $random, array $decorations) : ?Block{
		$totalWeight = 0;
		foreach($decorations as $decoration){
			$totalWeight += $decoration->getWeight();
		}
		$weight = $random->nextBoundedInt($totalWeight);
		foreach($decorations as $decoration){
			$weight -= $decoration->getWeight();
			if($weight < 0){
				return $decoration->getBlock();
			}
		}
		return null;
	}

	/** @var DoublePlantDecoration[] */
	private $doublePlants = [];

	final public function setDoublePlants(DoublePlantDecoration ...$doublePlants) : void{
		$this->doublePlants = $doublePlants;
	}

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = ($chunk->getX() << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunk->getZ() << 4) + $random->nextBoundedInt(16);
		$sourceY = $random->nextBoundedInt($chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f) + 32);

		$species = self::getRandomDoublePlant($random, $this->doublePlants);
		(new DoubleTallPlant($species))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
	}
}