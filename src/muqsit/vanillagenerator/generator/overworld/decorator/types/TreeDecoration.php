<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator\types;

final class TreeDecoration{

	private string $class;
	private int $weight;

	public function __construct(string $class, int $weight){
		$this->class = $class;
		$this->weight = $weight;
	}

	public function getWeight() : int{
		return $this->weight;
	}

	public function getClass() : string{
		return $this->class;
	}
}