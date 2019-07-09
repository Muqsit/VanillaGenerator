<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\Lake;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class LakeDecorator extends Decorator{

	/** @var Block */
	private $type;

	/** @var int */
	private $rarity;

	/** @var int */
	private $baseOffset;

	/**
	 * Creates a lake decorator.
	 *
	 * @param Block $type
	 * @param int $rarity
	 * @param int $baseOffset
	 */
	public function __construct(Block $type, int $rarity, int $baseOffset = 0){
		$this->type = $type;
		$this->rarity = $rarity;
		$this->baseOffset = $baseOffset;
	}

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		if($random->nextBoundedInt($this->rarity) === 0){
			$sourceX = ($chunk->getX() << 4) + $random->nextBoundedInt(16);
			$sourceZ = ($chunk->getZ() << 4) + $random->nextBoundedInt(16);
			$sourceY = $random->nextBoundedInt($world->getWorldHeight() - $this->baseOffset) + $this->baseOffset;
			if($this->type->getId() === BlockLegacyIds::STILL_LAVA && ($sourceY >= 64 || $random->nextBoundedInt(10) > 0)){
				return;
			}
			while($world->getBlockAt($sourceX, $sourceY, $sourceZ)->getId() === BlockLegacyIds::AIR && $sourceY > 5){
				--$sourceY;
			}
			if($sourceY >= 5){
				(new Lake($this->type))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
			}
		}
	}
}