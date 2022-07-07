<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class TallGrass extends TerrainObject{

	public function __construct(
		private Block $grass_type
	){}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		do{
			$this_block = $world->getBlockAt($source_x, $source_y, $source_z);
			--$source_y;
		}while(($this_block->getTypeId() === BlockTypeIds::AIR || $this_block instanceof Leaves) && $source_y > 0);
		++$source_y;
		$succeeded = false;
		$height = $world->getMaxY();
		for($i = 0; $i < 128; ++$i){
			$x = $source_x + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $source_z + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $source_y + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			$block_type = $world->getBlockAt($x, $y, $z)->getTypeId();
			$block_type_below = $world->getBlockAt($x, $y - 1, $z)->getTypeId();
			if($y < $height && $block_type === BlockTypeIds::AIR && ($block_type_below === BlockTypeIds::GRASS || $block_type_below === BlockTypeIds::DIRT)){
				$world->setBlockAt($x, $y, $z, $this->grass_type);
				$succeeded = true;
			}
		}
		return $succeeded;
	}
}