<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

abstract class Decorator implements Populator{

	protected int $amount = -PHP_INT_MAX;

	final public function setAmount(int $amount) : void{
		$this->amount = $amount;
	}

	abstract public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void;

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		for($i = 0; $i < $this->amount; ++$i){
			$this->decorate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}
}