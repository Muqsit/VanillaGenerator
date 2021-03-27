<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class MushroomDecorator extends Decorator{

	/** @var Block */
	private Block $type;

	/** @var bool */
	private bool $fixed_height_range = false;

	/** @var float */
	private float $density = 0.0;

	/**
	 * Creates a mushroom decorator for the overworld.
	 *
	 * @param Block $type {@link Material#BROWN_MUSHROOM} or {@link Material#RED_MUSHROOM}
	 */
	public function __construct(Block $type){
		$this->type = $type;
	}

	public function setUseFixedHeightRange() : MushroomDecorator{
		$this->fixed_height_range = true;
		return $this;
	}

	public function setDensity(float $density) : MushroomDecorator{
		$this->density = $density;
		return $this;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		if($random->nextFloat() < $this->density){
			$source_x = ($chunk_x << 4) + $random->nextBoundedInt(16);
			$source_z = ($chunk_z << 4) + $random->nextBoundedInt(16);
			$source_y = $chunk->getHighestBlockAt($source_x & 0x0f, $source_z & 0x0f);
			$source_y = $this->fixed_height_range ? $source_y : $random->nextBoundedInt($source_y << 1);

			$height = $world->getMaxY();
			for($i = 0; $i < 64; ++$i){
				$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

				$block = $world->getBlockAt($x, $y, $z);
				$below_below = $world->getBlockAt($x, $y - 1, $z);
				if($y < $height && $block->getId() === BlockLegacyIds::AIR){
					switch($below_below->getId()){
						case BlockLegacyIds::MYCELIUM:
						case BlockLegacyIds::PODZOL:
							$can_place_shroom = true;
							break;
						case BlockLegacyIds::GRASS:
							$can_place_shroom = ($block->getLightLevel() < 13);
							break;
						case BlockLegacyIds::DIRT:
							if($below_below->getMeta() === BlockLegacyMetadata::DIRT_NORMAL){
								$can_place_shroom = $block->getLightLevel() < 13;
							}else{
								$can_place_shroom = false;
							}
							break;
						default:
							$can_place_shroom = false;
					}
					if($can_place_shroom){
						$world->setBlockAt($x, $y, $z, $this->type);
					}
				}
			}
		}
	}
}