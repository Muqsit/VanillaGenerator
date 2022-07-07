<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator\types;

use pocketmine\block\DoublePlant;

final class DoublePlantDecoration{

	public function __construct(
		private DoublePlant $block,
		private int $weight
	){}

	public function getBlock() : DoublePlant{
		return $this->block;
	}

	public function getWeight() : int{
		return $this->weight;
	}
}