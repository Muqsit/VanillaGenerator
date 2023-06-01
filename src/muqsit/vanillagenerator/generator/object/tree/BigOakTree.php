<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\math\Axis;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function array_key_exists;

class BigOakTree extends GenericTree{

	private const LEAF_DENSITY = 1.0;

	private int $max_leaf_distance = 5;
	private int $trunk_height;

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(12) + 5);
	}

	final public function setMaxLeafDistance(int $distance) : void{
		$this->max_leaf_distance = $distance;
	}

	public function canPlace(int $base_x, int $base_y, int $base_z, ChunkManager $world) : bool{
		$from = new Vector3($base_x, $base_y, $base_z);
		$to = new Vector3($base_x, $base_y + $this->height - 1, $base_z);
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

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if(!$this->canPlaceOn($world->getBlockAt($source_x, $source_y - 1, $source_z)) || !$this->canPlace($source_x, $source_y, $source_z, $world)){
			return false;
		}

		$this->trunk_height = (int) ($this->height * 0.618);
		if($this->trunk_height >= $this->height){
			$this->trunk_height = $this->height - 1;
		}

		$leaf_nodes = $this->generateLeafNodes($source_x, $source_y, $source_z, $world, $random);

		// generate the leaves
		foreach($leaf_nodes as $node){
			for($y = 0; $y < $this->max_leaf_distance; ++$y){
				$size = $y > 0 && $y < $this->max_leaf_distance - 1.0 ? 3.0 : 2.0;
				$node_distance = (int) (0.618 + $size);
				for($x = -$node_distance; $x <= $node_distance; ++$x){
					for($z = -$node_distance; $z <= $node_distance; ++$z){
						$size_x = abs($x) + 0.5;
						$size_z = abs($z) + 0.5;
						if($size_x * $size_x + $size_z * $size_z <= $size * $size && array_key_exists($world->getBlockAt($node->x + $x, $node->y + $y, $node->z + $z)->getTypeId(), $this->overridables)){
							$this->transaction->addBlockAt($node->x + $x, $node->y + $y, $node->z + $z, $this->leaves_type);
						}
					}
				}
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->trunk_height; ++$y){
			$this->transaction->addBlockAt($source_x, $source_y + $y, $source_z, $this->log_type);
		}

		// generate the branches
		foreach($leaf_nodes as $node){
			if((float) ($node->branch_y - $source_y) >= $this->height * 0.2){
				$base = new Vector3($source_x, $node->branch_y, $source_z);
				$leaf_node = new Vector3($node->x, $node->y, $node->z);
				$branch = $leaf_node->subtractVector($base);
				$max_distance = max(abs($branch->getFloorY()), max(abs($branch->getFloorX()), abs($branch->getFloorZ())));
				if($max_distance > 0){
					$dx = (float) $branch->x / $max_distance;
					$dy = (float) $branch->y / $max_distance;
					$dz = (float) $branch->z / $max_distance;
					for($i = 0; $i <= $max_distance; ++$i){
						$branch = $base->add(0.5 + $i * $dx, 0.5 + $i * $dy, 0.5 + $i * $dz);
						$x = abs($branch->getFloorX() - $base->getFloorX());
						$z = abs($branch->getFloorZ() - $base->getFloorZ());
						$max = max($x, $z);
						$direction = $max > 0 ? ($max === $x ? Axis::X : Axis::Z) : Axis::Y;
						$this->transaction->addBlockAt($branch->getFloorX(), $branch->getFloorY(), $branch->getFloorZ(), $this->getLogType()->setAxis($direction));
					}
				}
			}
		}

		return true;
	}

	private function countAvailableBlocks(Vector3 $from, Vector3 $to, ChunkManager $world) : int{
		$n = 0;
		$target = $to->subtractVector($from);
		$max_distance = max(abs($target->getFloorY()), max(abs($target->getFloorX()), abs($target->getFloorZ())));
		if($max_distance > 0){
			$dx = (float) $target->x / $max_distance;
			$dy = (float) $target->y / $max_distance;
			$dz = (float) $target->z / $max_distance;
			$height = $world->getMaxY();
			for($i = 0; $i <= $max_distance; ++$i, ++$n){
				$target = $from->add(0.5 + $i * $dx, 0.5 + $i * $dy, 0.5 + $i * $dz);
				$target_floorY = $target->getFloorY();
				if($target_floorY < 0 || $target_floorY > $height || !array_key_exists($world->getBlockAt($target->getFloorX(), $target->getFloorY(), $target->getFloorZ())->getTypeId(), $this->overridables)){
					return $n;
				}
			}
		}
		return -1;
	}

	/**
	 * @param int $block_x
	 * @param int $block_y
	 * @param int $block_z
	 * @param ChunkManager $world
	 * @param Random $random
	 * @return LeafNode[]
	 */
	private function generateLeafNodes(int $block_x, int $block_y, int $block_z, ChunkManager $world, Random $random) : array{
		$leaf_nodes = [];
		$y = $block_y + $this->height = $this->max_leaf_distance;
		$trunk_top_y = $block_y + $this->trunk_height;
		$leaf_nodes[] = new LeafNode($block_x, $y, $block_z, $trunk_top_y);

		$node_count = (int) (1.382 + ((static::LEAF_DENSITY * (double) ($this->height / 13.0)) ** 2.0));
		$node_count = $node_count < 1 ? 1 : $node_count;

		for($l = --$y - $block_y; $l >= 0; --$l, --$y){
			$h = $this->height / 2.0;
			$v = $h - $l;
			$f = $l < ($this->height * 0.3) ? -1.0 : (
			$v === $h ? $h * 0.5 : (
			$h <= abs($v) ? 0.0 : (float) sqrt($h * $h - $v * $v) * 0.5
			)
			);
			if($f >= 0.0){
				for($i = 0; $i < $node_count; ++$i){
					$d1 = $f * ($random->nextFloat() + 0.328);
					$d2 = $random->nextFloat() * M_PI * 2.0;
					$x = (int) ($d1 * sin($d2) + $block_x + 0.5);
					$z = (int) ($d1 * cos($d2) + $block_z + 0.5);
					if($this->countAvailableBlocks(new Vector3($x, $y, $z), new Vector3($x, $y + $this->max_leaf_distance, $z), $world) === -1){
						$off_x = $block_x - $x;
						$off_z = $block_z - $z;
						$distance = 0.381 * hypot($off_x, $off_z);
						$branch_base_y = min($trunk_top_y, (int) ($y - $distance));
						if($this->countAvailableBlocks(new Vector3($x, $branch_base_y, $z), new Vector3($x, $y, $z), $world) === -1){
							$leaf_nodes[] = new LeafNode($x, $y, $z, $branch_base_y);
						}
					}
				}
			}
		}
		return $leaf_nodes;
	}
}

final class LeafNode{

	public function __construct(
		public int $x,
		public int $y,
		public int $z,
		public int $branch_y
	){}
}