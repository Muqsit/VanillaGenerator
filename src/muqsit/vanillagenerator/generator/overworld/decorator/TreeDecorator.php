<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use Exception;
use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\tree\GenericTree;
use muqsit\vanillagenerator\generator\overworld\decorator\types\TreeDecoration;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class TreeDecorator extends Decorator{

	/**
	 * @param Random $random
	 * @param TreeDecoration[] $decorations
	 * @return string a GenericTree class
	 */
	private static function getRandomTree(Random $random, array $decorations) : ?string{
		$totalWeight = 0;
		foreach($decorations as $decoration){
			$totalWeight += $decoration->getWeight();
		}

		$weight = $random->nextBoundedInt($totalWeight);
		foreach($decorations as $decoration){
			$weight -= $decoration->getWeight();
			if($weight < 0){
				return $decoration->getClass();
			}
		}

		return null;
	}

	/** @var TreeDecoration[] */
	private $trees;

	final public function setTrees(TreeDecoration ...$trees) : void{
		$this->trees = $trees;
	}

	public function populate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$treeAmount = $this->amount;
		if($random->nextBoundedInt(10) === 0){
			++$treeAmount;
		}

		for($i = 0; $i < $treeAmount; ++$i){
			$this->decorate($world, $random, $chunk);
		}
	}

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$sourceX = ($chunk->getX() << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunk->getZ() << 4) + $random->nextBoundedInt(16);
		$sourceY = $chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f);

		$class = self::getRandomTree($random, $this->trees);
		if($class !== null){
			$txn = new BlockTransaction($world);
			/** @var GenericTree $tree */
			try{
				$tree = new $class($random, $txn);
			}catch(Exception $ex){
				$tree = new GenericTree($random, $txn);
			}
			if($tree->generate($world, $random, $sourceX, $sourceY, $sourceZ)){
				$txn->apply();
			}
		}
	}
}