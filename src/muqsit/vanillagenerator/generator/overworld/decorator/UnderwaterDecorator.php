<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\BlockPatch;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class UnderwaterDecorator extends Decorator{

	/** @var Block */
	private Block $type;

	/** @var int */
	private int $horiz_radius;

	/** @var int */
	private int $vert_radius;

	/** @var int[] */
	private array $overridables;

	public function __construct(Block $type){
		$this->type = $type;
	}

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
			$this->overridables[] = $overridable->getFullId();
		}
		return $this;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = ($chunk_x << 4) + $random->nextBoundedInt(16);
		$source_z = ($chunk_z << 4) + $random->nextBoundedInt(16);
		$source_y = $chunk->getHighestBlockAt($source_x & 0x0f, $source_z & 0x0f) - 1;
		while(
			$source_y > 1 &&
			(
				($block_id = $world->getBlockAt($source_x, $source_y - 1, $source_z)->getId()) === BlockLegacyIds::STILL_WATER ||
				$block_id === BlockLegacyIds::FLOWING_WATER
			)
		){
			--$source_y;
		}
		$material = $world->getBlockAt($source_x, $source_y, $source_z)->getId();
		if($material === BlockLegacyIds::STILL_WATER || $material === BlockLegacyIds::FLOWING_WATER){
			(new BlockPatch($this->type, $this->horiz_radius, $this->vert_radius, ...$this->overridables))->generate($world, $random, $source_x, $source_y, $source_z);
		}
	}
}