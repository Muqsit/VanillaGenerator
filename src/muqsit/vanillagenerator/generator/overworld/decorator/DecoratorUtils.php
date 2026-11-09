<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use pocketmine\utils\Random;
use pocketmine\world\format\Chunk;

class DecoratorUtils{

	/**
	 * Get a safe Y coordinate for surface decoration that won't exceed world bounds
	 * 
	 * @param Random $random
	 * @param Chunk $chunk
	 * @param int $x
	 * @param int $z
	 * @param int $surface_offset Additional height above surface (default 32)
	 * @param int $max_y_limit Maximum Y coordinate allowed (default 160 for 1.18+ compatibility)
	 * @return int Safe Y coordinate for decoration
	 */
	public static function getSurfaceY(Random $random, Chunk $chunk, int $x, int $z, int $surface_offset = 32, int $max_y_limit = 160) : int{
		$highest_block = $chunk->getHighestBlockAt($x & Chunk::COORD_MASK, $z & Chunk::COORD_MASK);
		if($highest_block <= 0){
			return 0;
		}
		
		// Limit surface generation to reasonable heights for 1.18+
		$safe_max = min($highest_block + $surface_offset, $max_y_limit);
		return $random->nextBoundedInt($safe_max);
	}
	
	/**
	 * Get Y coordinate for plant generation near ground level
	 * 
	 * @param Random $random
	 * @param Chunk $chunk  
	 * @param int $x
	 * @param int $z
	 * @return int Safe Y coordinate for plant generation
	 */
	public static function getPlantY(Random $random, Chunk $chunk, int $x, int $z) : int{
		$highest_block = $chunk->getHighestBlockAt($x & Chunk::COORD_MASK, $z & Chunk::COORD_MASK);
		if($highest_block <= 0){
			return 0;
		}
		
		// Plants should generate close to surface level, max Y=120 for 1.18+
		$plant_max = min($highest_block + 16, 120);
		return $random->nextBoundedInt($plant_max);
	}
}