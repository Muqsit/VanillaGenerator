<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid\utils;

final class BiomeEdgeEntry{

	/** @var array<int, int> */
	public array $key;

	/** @var int[]|null */
	public ?array $value = null;

	/**
	 * @param array<int, int> $mapping
	 * @param int[] $value
	 */
	public function __construct(array $mapping, ?array $value = null){
		$this->key = $mapping;
		if($value !== null){
			$this->value = [];
			foreach($value as $v){
				$this->value[$v] = $v;
			}
		}
	}
}