<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockFactory;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class BigOakTree extends GenericTree{

	private const LEAF_DENSITY = 1.0;

	/** @var int */
	private $maxLeafDistance = 5;

	/** @var int */
	private $trunkHeight;

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(12) + 5);
	}

	final public function setMaxLeafDistance(int $distance) : void{
		$this->maxLeafDistance = $distance;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		$from = new Vector3($baseX, $baseY, $baseZ);
		$to = new Vector3($baseX, $baseY + $this->height - 1, $baseZ);
		$blocks = $this->countAvailableBlocks($from, $to, $world);
		if($blocks === -1){
			return true;
		}
		if($blocks > 5){
			$this->height = $blocks;
			return true;
		}
		return false;
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		if(!$this->canPlaceOn($world->getBlockAt($blockX, $blockY - 1, $blockZ)) || !$this->canPlace($blockX, $blockY, $blockZ, $world)){
			return false;
		}

		$this->trunkHeight = (int) ($this->height * 0.618);
		if($this->trunkHeight >= $this->height){
			$this->trunkHeight = $this->height - 1;
		}

		$leafNodes = $this->generateLeafNodes($blockX, $blockY, $blockZ, $world, $random);

		// generate the leaves
		foreach($leafNodes as $node){
			for($y = 0; $y < $this->maxLeafDistance; ++$y){
				$size = $y > 0 && $y < $this->maxLeafDistance - 1.0 ? 3.0 : 2.0;
				$nodeDistance = (int) (0.618 + $size);
				for($x = -$nodeDistance; $x <= $nodeDistance; ++$x){
					for($z = -$nodeDistance; $z <= $nodeDistance; ++$z){
						$sizeX = abs($x) + 0.5;
						$sizeZ = abs($z) + 0.5;
						if($sizeX * $sizeX + $sizeZ * $sizeZ <= $size * $size && isset($this->overridables[$world->getBlockAt($node->x + $x, $node->y + $y, $node->z + $z)->getId()])){
							$this->transaction->addBlockAt($node->x + $x, $node->y + $y, $node->z + $z, $this->leavesType);
						}
					}
				}
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->trunkHeight; ++$y){
			$this->transaction->addBlockAt($blockX, $blockY + $y, $blockZ, $this->logType);
		}

		// generate the branches
		foreach($leafNodes as $node){
			if((float) ($node->branchY - $blockY) >= $this->height * 0.2){
				$base = new Vector3($blockX, $node->branchY, $blockZ);
				$leafNode = new Vector3($node->x, $node->y, $node->z);
				$branch = $leafNode->subtract($base);
				$maxDistance = max(abs($branch->getFloorY()), max(abs($branch->getFloorX()), abs($branch->getFloorZ())));
				if($maxDistance > 0){
					$dx = (float) $branch->x / $maxDistance;
					$dy = (float) $branch->y / $maxDistance;
					$dz = (float) $branch->z / $maxDistance;
					for($i = 0; $i <= $maxDistance; ++$i){
						$branch = $base->add(0.5 + $i * $dx, 0.5 + $i * $dy, 0.5 + $i * $dz);
						$x = abs($branch->getFloorX() - $base->getFloorX());
						$z = abs($branch->getFloorZ() - $base->getFloorZ());
						$max = max($x, $z);
						$direction = $max > 0 ? ($max === $x ? 4 : 8) : 0; // EAST / SOUTH
						$this->transaction->addBlockAt($branch->getFloorX(), $branch->getFloorY(), $branch->getFloorZ(), BlockFactory::get($this->logType->getId(), $this->logType->getMeta() | $direction));
					}
				}
			}
		}

		return true;
	}

	private function countAvailableBlocks(Vector3 $from, Vector3 $to, ChunkManager $world) : int{
		$n = 0;
		$target = $to->subtract($from);
		$maxDistance = max(abs($target->getFloorY()), max(abs($target->getFloorX()), abs($target->getFloorZ())));
		if($maxDistance > 0){
			$dx = (float) $target->x / $maxDistance;
			$dy = (float) $target->y / $maxDistance;
			$dz = (float) $target->z / $maxDistance;
			$height = $world->getWorldHeight();
			for($i = 0; $i <= $maxDistance; ++$i, ++$n){
				$target = $from->add(0.5 + $i * $dx, 0.5 + $i * $dy, 0.5 + $i * $dz);
				$target_floorY = $target->getFloorY();
				if($target_floorY < 0 || $target_floorY > $height || !isset($this->overridables[$world->getBlockAt($target->getFloorX(), $target->getFloorY(), $target->getFloorZ())->getId()])){
					return $n;
				}
			}
		}
		return -1;
	}

	private function generateLeafNodes(int $blockX, int $blockY, int $blockZ, ChunkManager $world, Random $random) : array{
		$leafNodes = [];
		$y = $blockY + $this->height = $this->maxLeafDistance;
		$trunkTopY = $blockY + $this->trunkHeight;
		$leafNodes[] = new LeafNode($blockX, $y, $blockZ, $trunkTopY);

		$nodeCount = (int) (1.382 + ((static::LEAF_DENSITY * (double) ($this->height / 13.0)) ** 2.0));
		$nodeCount = $nodeCount < 1 ? 1 : $nodeCount;

		for($l = --$y - $blockY; $l >= 0; --$l, --$y){
			$h = $this->height / 2.0;
			$v = (float) ($h - $l);
			$f = $l < (float) ($this->height * 0.3) ? -1.0 : (
			$v === $h ? $h * 0.5 : (
			$h <= abs($v) ? 0.0 : (float) sqrt($h * $h - $v * $v) * 0.5
			)
			);
			if($f >= 0.0){
				for($i = 0; $i < $nodeCount; ++$i){
					$d1 = $f * ($random->nextFloat() + 0.328);
					$d2 = $random->nextFloat() * M_PI * 2.0;
					$x = (int) ($d1 * sin($d2) + $blockX + 0.5);
					$z = (int) ($d1 * cos($d2) + $blockZ + 0.5);
					if($this->countAvailableBlocks(new Vector3($x, $y, $z), new Vector3($x, $y + $this->maxLeafDistance, $z), $world) === -1){
						$offX = $blockX - $x;
						$offZ = $blockZ - $z;
						$distance = 0.381 * hypot($offX, $offZ);
						$branchBaseY = min($trunkTopY, (int) ($y - $distance));
						if($this->countAvailableBlocks(new Vector3($x, $branchBaseY, $z), new Vector3($x, $y, $z), $world) === -1){
							$leafNodes[] = new LeafNode($x, $y, $z, $branchBaseY);
						}
					}
				}
			}
		}
		return $leafNodes;
	}
}

final class LeafNode{

	/** @var int */
	public $x;

	/** @var int */
	public $y;

	/** @var int */
	public $z;

	/** @var int */
	public $branchY;

	public function __construct(int $x, int $y, int $z, int $branchY){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->branchY = $branchY;
	}
}