<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator\types;

use pocketmine\block\DoublePlant;

final class DoublePlantDecoration{

	/** @var DoublePlant */
	private $block;

	/** @var int */
	private $weight;

	public function __construct(DoublePlant $block, int $weight){
		$this->block = $block;
		$this->weight = $weight;
	}

	public function getBlock() : DoublePlant{
		return $this->block;
	}

	public function getWeight() : int{
		return $this->weight;
	}
}