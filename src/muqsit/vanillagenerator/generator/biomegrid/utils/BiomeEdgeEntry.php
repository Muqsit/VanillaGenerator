<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid\utils;

use Ds\Set;

final class BiomeEdgeEntry{

	/** @var array<int, int> */
	public $key;

	/** @var Set<int>|null */
	public $value;

	/**
	 * @param array<int, int> $mapping
	 * @param int[] $value
	 */
	public function __construct(array $mapping, ?array $value = null){
		$this->key = $mapping;
		$this->value = $value !== null ? new Set($value) : null;
	}
}