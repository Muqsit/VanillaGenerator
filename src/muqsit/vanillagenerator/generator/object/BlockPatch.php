<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use function array_key_exists;

class BlockPatch extends TerrainObject{

	private const MIN_RADIUS = 2;

	/** @var int[] */
	private array $overridables = [];

	/**
	 * Creates a patch.
	 * @param Block $type the ground cover block type
	 * @param int $horiz_radius the maximum radius on the horizontal plane
	 * @param int $vert_radius the depth above and below the center
	 * @param int ...$overridables_full_id
	 */
	public function __construct(
		private Block $type,
		private int $horiz_radius,
		private int $vert_radius,
		int ...$overridables_full_id
	){
		foreach($overridables_full_id as $full_id){
			$this->overridables[$full_id] = $full_id;
		}
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		$succeeded = false;
		$n = $random->nextBoundedInt($this->horiz_radius - self::MIN_RADIUS) + self::MIN_RADIUS;
		$nsquared = $n * $n;
		for($x = $source_x - $n; $x <= $source_x + $n; ++$x){
			for($z = $source_z - $n; $z <= $source_z + $n; ++$z){
				if(($x - $source_x) * ($x - $source_x) + ($z - $source_z) * ($z - $source_z) > $nsquared){
					continue;
				}
				for($y = $source_y - $this->vert_radius; $y <= $source_y + $this->vert_radius; ++$y){
					$block = $world->getBlockAt($x, $y, $z);
					if(!array_key_exists($block->getStateId(), $this->overridables)){
						continue;
					}

					if(TerrainObject::killWeakBlocksAbove($world, $x, $y, $z)){
						break;
					}

					$world->setBlockAt($x, $y, $z, $this->type);
					$succeeded = true;
					break;
				}
			}
		}
		return $succeeded;
	}
}