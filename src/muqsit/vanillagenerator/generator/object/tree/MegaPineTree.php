<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class MegaPineTree extends MegaRedwoodTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setLeavesHeight($random->nextBoundedInt(5) + 3);
	}

	public function generate(ChunkManager $world, Random $random, int $blockX, int $blockY, int $blockZ) : bool{
		$generated = parent::generate($world, $random, $blockX, $blockY, $blockZ);
		if($generated){
			$this->generatePodzol($blockX, $blockY, $blockZ, $world, $random);
		}
		return $generated;
	}

	protected function generateDirtBelowTrunk(int $blockX, int $blockY, int $blockZ) : void{
		// SELF, SOUTH, EAST, SOUTH EAST
		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ, VanillaBlocks::PODZOL());
		$this->transaction->addBlockAt($blockX, $blockY - 1, $blockZ + 1, VanillaBlocks::PODZOL());
		$this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ, VanillaBlocks::PODZOL());
		$this->transaction->addBlockAt($blockX + 1, $blockY - 1, $blockZ + 1, VanillaBlocks::PODZOL());
	}

	private function generatePodzol(int $sourceX, int $sourceY, int $sourceZ, ChunkManager $world, Random $random) : void{
		$this->generatePodzolPatch($sourceX - 1, $sourceY, $sourceZ - 1, $world);
		$this->generatePodzolPatch($sourceX + 2, $sourceY, $sourceZ - 1, $world);
		$this->generatePodzolPatch($sourceX - 1, $sourceY, $sourceZ + 2, $world);
		$this->generatePodzolPatch($sourceX + 2, $sourceY, $sourceZ + 2, $world);
		for($i = 0; $i < 5; ++$i){
			$n = $random->nextBoundedInt(64);
			if($n % 8 === 0 || $n % 8 === 7 || $n / 8 === 0 || $n / 8 === 7){
				$this->generatePodzolPatch($sourceX - 3 + $n % 8, $sourceY, $sourceZ - 3 + $n / 8, $world);
			}
		}
	}

	private function generatePodzolPatch(int $sourceX, int $sourceY, int $sourceZ, ChunkManager $world) : void{
		for($x = -2; $x <= 2; ++$x){
			for($z = -2; $z <= 2; ++$z){
				if(abs($x) === 2 && abs($z) === 2){
					continue;
				}
				for($y = 2; $y >= -3; --$y){
					$blockId = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z)->getId();
					if($blockId === BlockLegacyIds::GRASS || $blockId === BlockLegacyIds::DIRT){
						if($world->getBlockAt($sourceX + $x, $sourceY + $y + 1, $sourceZ + $z)->isSolid()){
							$dirt = VanillaBlocks::DIRT();
						}else{
							$dirt = VanillaBlocks::PODZOL();
						}
						$world->setBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z, $dirt);
					}elseif($blockId !== BlockLegacyIds::AIR && $sourceY + $y < $sourceY){
						break;
					}
				}
			}
		}
	}
}