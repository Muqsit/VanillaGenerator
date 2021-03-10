<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid\utils;

use muqsit\vanillagenerator\generator\biomegrid\MapLayer;

final class MapLayerPair{

	/** @var MapLayer */
	public MapLayer $high_resolution;

	/** @var MapLayer|null */
	public ?MapLayer $low_resolution;

	public function __construct(MapLayer $high_resolution, ?MapLayer $low_resolution){
		$this->high_resolution = $high_resolution;
		$this->low_resolution = $low_resolution;
	}
}