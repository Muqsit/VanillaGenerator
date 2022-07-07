<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\biomegrid\utils;

use muqsit\vanillagenerator\generator\biomegrid\MapLayer;

final class MapLayerPair{

	public function __construct(
		public MapLayer $high_resolution,
		public ?MapLayer $low_resolution
	){}
}