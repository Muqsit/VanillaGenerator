<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\Block;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class BlockPatch extends TerrainObject{

	private const MIN_RADIUS = 2;

	/** @var Block */
	private $type;

	/** @var int */
	private $horizRadius;

	/** @var int */
	private $vertRadius;

	/** @var int[] */
	private $overridables;

	/**
	 * Creates a patch.
	 * @param Block $type the ground cover block type
	 * @param int $horizRadius the maximum radius on the horizontal plane
	 * @param int $vertRadius the depth above and below the center
	 * @param int ...$overridables_full_id
	 */
	public function __construct(Block $type, int $horizRadius, int $vertRadius, int ...$overridables_full_id){
		$this->type = $type;
		$this->horizRadius = $horizRadius;
		$this->vertRadius = $vertRadius;
		$this->overridables = $overridables_full_id;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$succeeded = false;
		$n = $random->nextBoundedInt($this->horizRadius - self::MIN_RADIUS) + self::MIN_RADIUS;
		$nsquared = $n * $n;
		for($x = $sourceX - $n; $x <= $sourceX + $n; ++$x){
			for($z = $sourceZ - $n; $z <= $sourceZ + $n; ++$z){
				if(($x - $sourceX) * ($x - $sourceX) + ($z - $sourceZ) * ($z - $sourceZ) > $nsquared){
					continue;
				}
				for($y = $sourceY - $this->vertRadius; $y <= $sourceY + $this->vertRadius; ++$y){
					$block = $world->getBlockAt($x, $y, $z);
					if(!in_array($block->getFullId(), $this->overridables, true)){
						continue;
					}
					if(TerrainObject::killPlantAbove($world, $x, $y, $z)){
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