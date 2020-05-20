<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\Flower;
use muqsit\vanillagenerator\generator\overworld\decorator\types\FlowerDecoration;
use pocketmine\block\Block;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class FlowerDecorator extends Decorator{

	/**
	 * @param Random $random
	 * @param FlowerDecoration[] $decorations
	 * @return Block|null
	 */
	private static function getRandomFlower(Random $random, array $decorations) : ?Block{
		$totalWeight = 0;
		foreach($decorations as $decoration){
			$totalWeight += $decoration->getWeight();
		}

		if($totalWeight > 0){
			$weight = $random->nextBoundedInt($totalWeight);
			foreach($decorations as $decoration){
				$weight -= $decoration->getWeight();
				if($weight < 0){
					return $decoration->getBlock();
				}
			}
		}

		return null;
	}

	/** @var FlowerDecoration[] */
	private $flowers = [];

	final public function setFlowers(FlowerDecoration ...$flowers) : void{
		$this->flowers = $flowers;
	}

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$sourceY = $random->nextBoundedInt($chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) + 32);

		// the flower can change on each decoration pass
		$flower = self::getRandomFlower($random, $this->flowers);
		if($flower !== null){
			(new Flower($flower))->generate($world, $random, ($chunk->getX() << 4) + $x, $sourceY, ($chunk->getZ() << 4) + $z);
		}
	}
}