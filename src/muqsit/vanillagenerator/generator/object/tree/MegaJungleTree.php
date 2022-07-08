<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use function array_key_exists;

class MegaJungleTree extends GenericTree{

	/**
	 * Initializes this tree with a random height, preparing it to attempt to generate.
	 * @param Random $random
	 * @param BlockTransaction $transaction
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(20) + $random->nextBoundedInt(3) + 10);
		$this->setType(VanillaBlocks::JUNGLE_LOG(), VanillaBlocks::JUNGLE_LEAVES());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getTypeId();
		return $id === BlockTypeIds::GRASS || $id === BlockTypeIds::DIRT;
	}

	public function canPlace(int $base_x, int $base_y, int $base_z, ChunkManager $world) : bool{
		for($y = $base_y; $y <= $base_y + 1 + $this->height; ++$y){
			// Space requirement
			$radius = match(true){
				$y === $base_y => 1, // radius at source block y is 1 (only trunk)
				$y >= $base_y + 1 + $this->height - 2 => 2, // max radius starting at leaves bottom
				default => 2 // default radius if above first block
			}; // TODO: check whether this is correct - 2nd branch and default branch have same values
			// check for block collision on horizontal slices
			for($x = $base_x - $radius; $x <= $base_x + $radius; ++$x){
				for($z = $base_z - $radius; $z <= $base_z + $radius; ++$z){
					if($y >= 0 && $y < World::Y_MAX){
						// we can overlap some blocks around
						if(!array_key_exists($world->getBlockAt($x, $y, $z)->getTypeId(), $this->overridables)){
							return false;
						}
					}else{ // height out of range
						return false;
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		// generates the canopy leaves
		for($y = -2; $y <= 0; ++$y){
			$this->generateLeaves($source_x + 0, $source_y + $this->height + $y, $source_z, 3 - $y, false, $world);
		}

		// generates the branches
		$branch_height = $this->height - 2 - $random->nextBoundedInt(4);
		while($branch_height > $this->height / 2){ // branching start at least at middle height
			$x = 0;
			$z = 0;
			// generates a branch
			$d = $random->nextFloat() * M_PI * 2.0; // random direction
			for($i = 0; $i < 5; ++$i){
				// branches are always longer when facing south or east (positive X or positive Z)
				$x = (int) (cos($d) * $i + 1.5);
				$z = (int) (sin($d) * $i + 1.5);
				$this->transaction->addBlockAt($source_x + $x, (int) ($source_y + $branch_height - 3 + $i / 2), $source_z + $z, $this->log_type);
			}
			// generates leaves for this branch
			for($y = $branch_height - ($random->nextBoundedInt(2) + 1); $y <= $branch_height; ++$y){
				$this->generateLeaves($source_x + $x, $source_y + $y, $source_z + $z, 1 - ($y - $branch_height), true, $world);
			}
			$branch_height -= $random->nextBoundedInt(4) + 2;
		}

		// generates the trunk
		$this->generateTrunk($world, $source_x, $source_y, $source_z);

		// add some vines on the trunk
		$this->addVinesOnTrunk($world, $source_x, $source_y, $source_z, $random);

		// blocks below trunk are always dirt
		$this->generateDirtBelowTrunk($source_x, $source_y, $source_z);
		return true;
	}

	protected function generateLeaves(int $source_x, int $source_y, int $source_z, int $radius, bool $odd, ChunkManager $world) : void{
		$n = 1;
		if($odd){
			$n = 0;
		}
		for($x = $source_x - $radius; $x <= $source_x + $radius + $n; ++$x){
			$radius_x = $x - $source_x;
			for($z = $source_z - $radius; $z <= $source_z + $radius + $n; ++$z){
				$radius_z = $z - $source_z;

				$sq_x = $radius_x * $radius_x;
				$sq_z = $radius_z * $radius_z;
				$sq_r = $radius * $radius;
				$sq_xb = ($radius_x - $n) * ($radius_x - $n);
				$sq_zb = ($radius_z - $n) * ($radius_z - $n);

				if($sq_x + $sq_z <= $sq_r || $sq_xb + $sq_zb <= $sq_r || $sq_x + $sq_zb <= $sq_r || $sq_xb + $sq_z <= $sq_r){
					$this->replaceIfAirOrLeaves($x, $source_y, $z, $this->leaves_type, $world);
				}
			}
		}
	}

	protected function generateTrunk(ChunkManager $world, int $block_x, int $block_y, int $block_z) : void{
		// SELF, SOUTH, EAST, SOUTH EAST
		for($y = 0; $y < $this->height + -1; ++$y){
			$type = $world->getBlockAt($block_x + 0, $block_y + $y, $block_z + 0);
			if($type->getTypeId() === BlockTypeIds::AIR || $type instanceof Leaves){
				$this->transaction->addBlockAt($block_x + 0, $block_y + $y, $block_z, $this->log_type);
			}
			$type = $world->getBlockAt($block_x + 0, $block_y + $y, $block_z + 1);
			if($type->getTypeId() === BlockTypeIds::AIR || $type instanceof Leaves){
				$this->transaction->addBlockAt($block_x + 0, $block_y + $y, $block_z + 1, $this->log_type);
			}
			$type = $world->getBlockAt($block_x + 1, $block_y + $y, $block_z + 0);
			if($type->getTypeId() === BlockTypeIds::AIR || $type instanceof Leaves){
				$this->transaction->addBlockAt($block_x + 1, $block_y + $y, $block_z, $this->log_type);
			}
			$type = $world->getBlockAt($block_x + 1, $block_y + $y, $block_z + 1);
			if($type->getTypeId() === BlockTypeIds::AIR || $type instanceof Leaves){
				$this->transaction->addBlockAt($block_x + 1, $block_y + $y, $block_z + 1, $this->log_type);
			}
		}
	}

	protected function generateDirtBelowTrunk(int $block_x, int $block_y, int $block_z) : void{
		// SELF, SOUTH, EAST, SOUTH EAST
		$dirt = VanillaBlocks::DIRT();
		$this->transaction->addBlockAt($block_x + 0, $block_y + -1, $block_z, $dirt);
		$this->transaction->addBlockAt($block_x + 0, $block_y + -1, $block_z + 1, $dirt);
		$this->transaction->addBlockAt($block_x + 1, $block_y + -1, $block_z, $dirt);
		$this->transaction->addBlockAt($block_x + 1, $block_y + -1, $block_z + 1, $dirt);
	}

	private function addVinesOnTrunk(ChunkManager $world, int $block_x, int $block_y, int $block_z, Random $random) : void{
		for($y = 1; $y < $this->height; ++$y){
			$this->maybePlaceVine($world, $block_x + -1, $block_y + $y, $block_z + 0, Facing::EAST, $random);
			$this->maybePlaceVine($world, $block_x + 0, $block_y + $y, $block_z + -1, Facing::SOUTH, $random);
			$this->maybePlaceVine($world, $block_x + 2, $block_y + $y, $block_z + 0, Facing::WEST, $random);
			$this->maybePlaceVine($world, $block_x + 1, $block_y + $y, $block_z + -1, Facing::SOUTH, $random);
			$this->maybePlaceVine($world, $block_x + 2, $block_y + $y, $block_z + 1, Facing::WEST, $random);
			$this->maybePlaceVine($world, $block_x + 1, $block_y + $y, $block_z + 2, Facing::NORTH, $random);
			$this->maybePlaceVine($world, $block_x + -1, $block_y + $y, $block_z + 1, Facing::EAST, $random);
			$this->maybePlaceVine($world, $block_x + 0, $block_y + $y, $block_z + 2, Facing::NORTH, $random);
		}
	}

	private function maybePlaceVine(ChunkManager $world, int $absolute_x, int $absolute_y, int $absolute_z, int $face_direction, Random $random) : void{
		if(
			$random->nextBoundedInt(3) !== 0 &&
			$world->getBlockAt($absolute_x, $absolute_y, $absolute_z)->getTypeId() === BlockTypeIds::AIR
		){
			$this->transaction->addBlockAt($absolute_x, $absolute_y, $absolute_z, VanillaBlocks::VINES()->setFace($face_direction, true));
		}
	}

}