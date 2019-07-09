<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator\types;

final class TreeDecoration{

	/** @var string */
	private $class;

	/** @var int */
	private $weight;

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