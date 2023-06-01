<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator\types;

use muqsit\vanillagenerator\generator\object\tree\GenericTree;

final class TreeDecoration{

	/**
	 * @param class-string<GenericTree> $class
	 * @param int $weight
	 */
	public function __construct(
		readonly public string $class,
		readonly public int $weight
	){}
}