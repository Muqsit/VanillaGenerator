<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld;

use InvalidArgumentException;

final class WorldType{

	public static function fromString(string $string) : string{
		return match(strtolower($string)){
			"amplified" => self::AMPLIFIED,
			"default_1_1", "version_1_1" => self::VERSION_1_1,
			"flat" => self::FLAT,
			"largebiomes", "large_biomes" => self::LARGE_BIOMES,
			"normal" => self::NORMAL,
			default => throw new InvalidArgumentException("Could not convert string \"{$string}\" to a " . self::class . " constant")
		};
	}

	public const AMPLIFIED = "AMPLIFIED";
	public const FLAT = "FLAT";
	public const LARGE_BIOMES = "LARGEBIOMES";
	public const NORMAL = "DEFAULT";
	public const VERSION_1_1 = "DEFAULT_1_1";

	private function __construct(){
	}
}