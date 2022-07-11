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

	public function decorate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		if($random->nextBoundedInt(8) !== 0){
			return;
		}

		$cx = $chunk_x << Chunk::COORD_BIT_SIZE;
		$cz = $chunk_z << Chunk::COORD_BIT_SIZE;

		$start_cx = $random->nextBoundedInt(16);
		$start_cz = $random->nextBoundedInt(16);
		$start_y = $chunk->getHeightMap($start_cx, $start_cz);
		if($start_y > 128){
			return;
		}

		$octaves = PerlinOctaveGenerator::fromRandomAndOctaves($random, 3, 4, 2, 4);
		$noise = $octaves->getFractalBrownianMotion($cx, $cz, 0, 0.5, 0.2);
		$angles = [];
		for($i = 0, $noise_c = count($noise); $i < $noise_c; ++$i){
			$angles[$i] = 360.0 * $noise[$i];
		}
		$section_count = count($angles) / 2;
		$nodes = [];
		$start_block_pos = new Vector3($cx + $start_cx, $start_y, $cz + $start_cz);
		$current_node = $start_block_pos->asVector3();
		$nodes[] = $current_node->asVector3();
		$length = 5;
		for($i = 0; $i < $section_count; ++$i){
			$yaw = $angles[$i + $section_count];
			$delta_y = -abs((int) floor($noise[$i] * $length));
			$delta_x = (int) floor((float) $length * cos(deg2rad($yaw)));
			$delta_z = (int) floor((float) $length * sin(deg2rad($yaw)));
			$current_node = new Vector3($delta_x, $delta_y, $delta_z);
			$node[] = $current_node->floor();
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
		$block_x = $block->x;
		$block_y = $block->y;
		$block_z = $block->z;
		for($x = $block_x - $radius; $x <= $block_x + $radius; ++$x){
			for($y = $block_y - $radius; $y <= $block_y + $radius; ++$y){
				for($z = $block_z - $radius; $z <= $block_z + $radius; ++$z){
					$distance_squared = ($block_x - $x) * ($block_x - $x) + ($block_y - $y) * ($block_y - $y) + ($block_z - $z) * ($block_z - $z);
					if($distance_squared < $radius * $radius){
						$world->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
					}
				}
			}
		}
	}
}