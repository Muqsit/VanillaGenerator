<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\object\BlockPatch;
use muqsit\vanillagenerator\generator\object\IceSpike;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class IceDecorator extends Decorator{

	/** @var int[] */
	private static array $OVERRIDABLES;

	public static function init() : void{
		self::$OVERRIDABLES = [
			VanillaBlocks::DIRT()->getFullId(),
			VanillaBlocks::GRASS()->getFullId(),
			VanillaBlocks::SNOW()->getFullId(),
			VanillaBlocks::ICE()->getFullId()
		];
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$source_x = $chunk_x << 4;
		$source_z = $chunk_z << 4;

		for($i = 0; $i < 3; ++$i){
			$x = $source_x + $random->nextBoundedInt(16);
			$z = $source_z + $random->nextBoundedInt(16);
			$y = $chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) - 1;
			while($y > 2 && $world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR){
				--$y;
			}
			if($world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::SNOW_BLOCK){
				(new BlockPatch(VanillaBlocks::PACKED_ICE(), 4, 1, ...self::$OVERRIDABLES))->generate($world, $random, $x, $y, $z);
			}
		}

		for($i = 0; $i < 2; ++$i){
			$x = $source_x + $random->nextBoundedInt(16);
			$z = $source_z + $random->nextBoundedInt(16);
			$y = $chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f);
			while($y > 2 && $world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::AIR){
				--$y;
			}
			if($world->getBlockAt($x, $y, $z)->getId() === BlockLegacyIds::SNOW_BLOCK){
				(new IceSpike())->generate($world, $random, $x, $y, $z);
			}
		}
	}

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
	}
}

IceDecorator::init();