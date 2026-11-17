<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator;

use pocketmine\utils\Random;
use RuntimeException;

final class BufferedRandom extends Random{

	private int $index = 0;

	/** @param array<int, int> $words */
	public function __construct(int $seed, private array $words){
		parent::__construct($seed);
	}

	public function nextInt() : int{
		$value = $this->words[++$this->index] ?? throw new RuntimeException("RNG buffer exhausted");
		return $value & 0x7fffffff;
	}

	public function nextBoundedInt(int $bound) : int{
		$value = $this->words[++$this->index] ?? throw new RuntimeException("RNG buffer exhausted");
		return ($value & 0x7fffffff) % $bound;
	}

	public function nextSignedInt() : int{
		$value = $this->words[++$this->index] ?? throw new RuntimeException("RNG buffer exhausted");
		return $value >= 0x80000000 ? $value - 0x100000000 : $value;
	}

	public function sync(Random $original) : void{
		$i = $this->index;
		$i >= 4 || throw new RuntimeException("Insufficient RNG values consumed");
		static $_sync = fn($x, $y, $z, $w) => [$this->x, $this->y, $this->z, $this->w] = [$x, $y, $z, $w];
		$_sync->call($original, $this->words[$i - 3], $this->words[$i - 2], $this->words[$i - 1], $this->words[$i]);
	}
}