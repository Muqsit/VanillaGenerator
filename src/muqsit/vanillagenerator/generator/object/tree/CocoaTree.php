<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\CocoaBlock;
use pocketmine\block\Leaves;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class CocoaTree extends JungleTree{

	private const COCOA_FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	// basically ages?
	private const SIZE_SMALL = CocoaBlock::MAX_AGE - 2;
	private const SIZE_MEDIUM = CocoaBlock::MAX_AGE - 1;
	private const SIZE_LARGE = CocoaBlock::MAX_AGE;

	private const COCOA_SIZE = [self::SIZE_SMALL, self::SIZE_MEDIUM, self::SIZE_LARGE];

	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if(!parent::generate($world, $random, $source_x, $source_y, $source_z)){
			return false;
		}

		// places some vines on the trunk
		$this->addVinesOnTrunk($source_x, $source_y, $source_z, $world, $random);
		// search for air around leaves to grow hanging vines
		$this->addVinesOnLeaves($source_x, $source_y, $source_z, $world, $random);
		// and maybe place some cocoa
		$this->addCocoa($source_x, $source_y, $source_z, $random);
		return true;
	}

	protected function addVinesOnLeaves(int $base_x, int $base_y, int $base_z, ChunkManager $world, Random $random) : void{
		for($y = $base_y - 3 + $this->height; $y <= $base_y + $this->height; ++$y){
			$ny = $y - ($base_y + $this->height);
			$radius = 2 - $ny / 2;
			for($x = $base_x - $radius; $x <= $base_x + $radius; ++$x){
				$ax = (int) $x;
				for($z = $base_z - $radius; $z <= $base_z + $radius; ++$z){
					$az = (int) $z;
					if($world->getBlockAt($ax, $y, $az) instanceof Leaves){
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax - 1, $y, $az)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax - 1, $y, $az, Facing::EAST, $world);
						}
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax + 1, $y, $az)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax + 1, $y, $az, Facing::WEST, $world);
						}
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax, $y, $az - 1)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax, $y, $az - 1, Facing::SOUTH, $world);
						}
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax, $y, $az + 1)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax, $y, $az + 1, Facing::NORTH, $world);
						}
					}
				}
			}
		}
	}

	private function addVinesOnTrunk(int $trunk_x, int $trunk_y, int $trunk_z, ChunkManager $world, Random $random) : void{
		for($y = 1; $y < $this->height; ++$y){
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunk_x - 1, $trunk_y + $y, $trunk_z)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunk_x - 1, $trunk_y + $y, $trunk_z, VanillaBlocks::VINES()->setFace(Facing::EAST, true));
			}
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunk_x + 1, $trunk_y + $y, $trunk_z)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunk_x + 1, $trunk_y + $y, $trunk_z, VanillaBlocks::VINES()->setFace(Facing::WEST, true));
			}
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunk_x, $trunk_y + $y, $trunk_z - 1)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunk_x, $trunk_y + $y, $trunk_z - 1, VanillaBlocks::VINES()->setFace(Facing::SOUTH, true));
			}
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunk_x, $trunk_y + $y, $trunk_z + 1)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunk_x, $trunk_y + $y, $trunk_z + 1, VanillaBlocks::VINES()->setFace(Facing::NORTH, true));
			}
		}
	}

	private function addHangingVine(int $x, int $y, int $z, int $face, ChunkManager $world) : void{
		for($i = 0; $i < 5; ++$i){
			if($world->getBlockAt($x, $y - $i, $z)->getTypeId() !== BlockTypeIds::AIR){
				break;
			}
			$this->transaction->addBlockAt($x, $y - $i, $z, VanillaBlocks::VINES()->setFace($face, true));
		}
	}

	private function addCocoa(int $source_x, int $source_y, int $source_z, Random $random) : void{
		if($this->height > 5 && $random->nextBoundedInt(5) === 0){
			for($y = 0; $y < 2; ++$y){
				foreach(self::COCOA_FACES as $cocoa_face){
					if($random->nextBoundedInt(count(self::COCOA_FACES) - $y) === 0){ // higher it is, more chances there is
						$size = self::COCOA_SIZE[$random->nextBoundedInt(count(self::COCOA_SIZE))];
						$block = (new Vector3($source_x, $source_y + $this->height - 5 + $y, $source_z))->getSide($cocoa_face);
						$this->transaction->addBlockAt($block->x, $block->y, $block->z, VanillaBlocks::COCOA_POD()->setFacing($cocoa_face)->setAge($size));
					}
				}
			}
		}
	}
}