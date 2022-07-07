<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator\types;

use pocketmine\block\Block;

final class FlowerDecoration{

	public function __construct(
		private Block $block,
		private int $weight
	){}

	public function getBlock() : Block{
		return $this->block;
	}

	public function getWeight() : int{
		return $this->weight;
	}
}