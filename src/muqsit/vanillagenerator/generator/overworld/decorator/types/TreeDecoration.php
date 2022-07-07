<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator\types;

final class TreeDecoration{

	public function __construct(
		private string $class,
		private int $weight
	){}

	public function getWeight() : int{
		return $this->weight;
	}

	public function getClass() : string{
		return $this->class;
	}
}