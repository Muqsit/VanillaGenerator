<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\utils;

use muqsit\vanillagenerator\generator\noise\bukkit\OctaveGenerator;

class WorldOctaves{

	/** @var OctaveGenerator */
	public $height;

	/** @var OctaveGenerator */
	public $roughness;

	/** @var OctaveGenerator */
	public $roughness2;

	/** @var OctaveGenerator */
	public $detail;

	/** @var OctaveGenerator */
	public $surface;

	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness2,
		OctaveGenerator $detail,
		OctaveGenerator $surface
	){
		$this->height = $height;
		$this->roughness = $roughness;
		$this->roughness2 = $roughness2;
		$this->detail = $detail;
		$this->surface = $surface;
	}
}