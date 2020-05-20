<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\utils;

use muqsit\vanillagenerator\generator\noise\bukkit\OctaveGenerator;

class NetherWorldOctaves extends WorldOctaves{

	/** @var OctaveGenerator */
	public $soul_sand;

	/** @var OctaveGenerator */
	public $gravel;

	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness2,
		OctaveGenerator $detail,
		OctaveGenerator $surface,
		OctaveGenerator $soul_sand,
		OctaveGenerator $gravel
	){
		parent::__construct($height, $roughness, $roughness2, $detail, $surface);
		$this->soul_sand = $soul_sand;
		$this->gravel = $gravel;
	}
}