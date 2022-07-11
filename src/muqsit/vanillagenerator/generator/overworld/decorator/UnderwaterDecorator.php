<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\BlockPatch;
use pocketmine\block\Block;
use pocketmine\block\Water;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class UnderwaterDecorator extends Decorator{

	private int $horiz_radius;
	private int $vert_radius;

	/** @var int[] */
	private array $overridables;

	public function __construct(
		private Block $type
	){}

	/**
	 * Updates the size of this decorator.
	 *
	 * @param int $horiz_radius the maximum radius on the horizontal plane
	 * @param int $vert_radius the depth above and below the center
	 * @return UnderwaterDecorator this, updated
	 */
	final public function setRadii(int $horiz_radius, int $vert_radius) : UnderwaterDecorator{
		$this->horiz_radius = $horiz_radius;
		$this->vert_radius = $vert_radius;
		return $this;
	}

	final public function setOverridableBlocks(Block ...$overridables) : UnderwaterDecorator{
		foreach($overridables as $overridable){
			$this->overridables[] = $overridable->getStateId();
		}
		return $this;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << Chunk::COORD_BIT_SIZE) + $random->nextBoundedInt(16);
		$source_y = $chunk->getHighestBlockAt($source_x & Chunk::COORD_MASK, $source_z & Chunk::COORD_MASK) - 1;
		while(
			$source_y > 1 &&
			$world->getBlockAt($source_x, $source_y - 1, $source_z) instanceof Water
		){
			--$source_y;
		}
		$material = $world->getBlockAt($source_x, $source_y, $source_z);
		if($material instanceof Water){
			(new BlockPatch($this->type, $this->horiz_radius, $this->vert_radius, ...$this->overridables))->generate($world, $random, $source_x, $source_y, $source_z);
		}
	}
}