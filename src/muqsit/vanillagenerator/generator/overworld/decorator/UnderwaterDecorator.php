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
	private $type;

	/** @var int */
	private $horizRadius;

	/** @var int */
	private $vertRadius;

	/** @var int[] */
	private $overridables;

	public function __construct(Block $type){
		$this->type = $type;
	}

	/**
	 * Updates the size of this decorator.
	 *
	 * @param int $horizRadius the maximum radius on the horizontal plane
	 * @param int $vertRadius the depth above and below the center
	 * @return UnderwaterDecorator this, updated
	 */
	final public function setRadii(int $horizRadius, int $vertRadius) : UnderwaterDecorator{
		$this->horizRadius = $horizRadius;
		$this->vertRadius = $vertRadius;
		return $this;
	}

	final public function setOverridableBlocks(Block ...$overridables) : UnderwaterDecorator{
		foreach($overridables as $overridable){
			$this->overridables[] = $overridable->getFullId();
		}
		return $this;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
		$sourceY = $chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f) - 1;
		while(
			$sourceY > 1 &&
			(
				($block_id = $world->getBlockAt($sourceX, $sourceY - 1, $sourceZ)->getId()) === BlockLegacyIds::STILL_WATER ||
				$block_id === BlockLegacyIds::FLOWING_WATER
			)
		){
			--$sourceY;
		}
		$material = $world->getBlockAt($sourceX, $sourceY, $sourceZ)->getId();
		if($material === BlockLegacyIds::STILL_WATER || $material === BlockLegacyIds::FLOWING_WATER){
			(new BlockPatch($this->type, $this->horizRadius, $this->vertRadius, ...$this->overridables))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
		}
	}
}