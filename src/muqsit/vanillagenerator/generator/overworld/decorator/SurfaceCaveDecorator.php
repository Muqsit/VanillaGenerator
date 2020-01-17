<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\decorator;

use muqsit\vanillagenerator\generator\Decorator;
use muqsit\vanillagenerator\generator\noise\glowstone\PerlinOctaveGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class SurfaceCaveDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		if($random->nextBoundedInt(8) !== 0){
			return;
		}

		$cx = $chunk->getX() << 4;
		$cz = $chunk->getZ() << 4;

		$startCx = $random->nextBoundedInt(16);
		$startCz = $random->nextBoundedInt(16);
		$startY = $chunk->getHeightMap($startCx, $startCz);
		if($startY > 128){
			return;
		}

		$octaves = PerlinOctaveGenerator::fromRandomAndOctaves($random, 3, 4, 2, 4);
		$noise = $octaves->getFractalBrownianMotion($cx, $cz, 0, 0.5, 0.2);
		$angles = [];
		for($i = 0, $noise_c = count($noise); $i < $noise_c; ++$i){
			$angles[$i] = 360.0 * $noise[$i];
		}
		$sectionCount = count($angles) / 2;
		$nodes = [];
		$startBlockPos = new Vector3($cx + $startCx, $startY, $cz + $startCz);
		$currentNode = $startBlockPos->asVector3();
		$nodes[] = $currentNode->asVector3();
		$length = 5;
		for($i = 0; $i < $sectionCount; ++$i){
			$yaw = $angles[$i + $sectionCount];
			$deltaY = -abs((int) floor($noise[$i] * $length));
			$deltaX = (int) floor((float) $length * cos(deg2rad($yaw)));
			$deltaZ = (int) floor((float) $length * sin(deg2rad($yaw)));
			$currentNode = new Vector3($deltaX, $deltaY, $deltaZ);
			$node[] = $currentNode->floor();
		}
		foreach($nodes as $node){
			if($node->y < 4){
				continue;
			}

			$this->caveAroundRay($world, $node, $random);
		}
	}

	private function caveAroundRay(ChunkManager $world, Vector3 $block, Random $random) : void{
		$radius = $random->nextBoundedInt(2) + 2;
		$blockX = $block->x;
		$blockY = $block->y;
		$blockZ = $block->z;
		for($x = $blockX - $radius; $x <= $blockX + $radius; ++$x){
			for($y = $blockY - $radius; $y <= $blockY + $radius; ++$y){
				for($z = $blockZ - $radius; $z <= $blockZ + $radius; ++$z){
					$distanceSquared = ($blockX - $x) * ($blockX - $x) + ($blockY - $y) * ($blockY - $y) + ($blockZ - $z) * ($blockZ - $z);
					if($distanceSquared < $radius * $radius){
						$world->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
					}
				}
			}
		}
	}
}