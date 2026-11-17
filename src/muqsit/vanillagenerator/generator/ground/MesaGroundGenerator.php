<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\ground;

use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function array_fill;
use function round;

class MesaGroundGenerator extends GroundGenerator{

	public const NORMAL = 0;
	public const BRYCE = 1;
	public const FOREST = 2;

	private int $type;

	/** @var DyeColor[]|null[] */
	private array $color_layer;

	private ?SimplexOctaveGenerator $color_noise = null;
	private ?SimplexOctaveGenerator $canyon_height_noise = null;
	private ?SimplexOctaveGenerator $canyon_scale_noise = null;
	private ?int $seed = null;

	public function __construct(int $type = self::NORMAL){
		parent::__construct(VanillaBlocks::RED_SAND(), VanillaBlocks::STAINED_CLAY()->setColor(DyeColor::ORANGE));
		$this->type = $type;
	}

	private function initialize(int $seed) : void{
		if($seed !== $this->seed || $this->color_noise === null || $this->canyon_scale_noise === null || $this->canyon_height_noise === null){
			$random = new Random($seed);
			$this->color_noise = SimplexOctaveGenerator::fromRandomAndOctaves($random, 1, 0, 0, 0);
			$this->color_noise->setScale(1 / 512.0);
			$this->initializeColorLayers($random);

			$this->canyon_height_noise = SimplexOctaveGenerator::fromRandomAndOctaves($random, 4, 0, 0, 0);
			$this->canyon_height_noise->setScale(1 / 4.0);
			$this->canyon_scale_noise = SimplexOctaveGenerator::fromRandomAndOctaves($random, 1, 0, 0, 0);
			$this->canyon_scale_noise->setScale(1 / 512.0);
			$this->seed = $seed;
		}
	}

	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surface_noise) : void{
		$chunk = $world->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		if($chunk === null){
			return;
		}

		$this->initialize($random->getSeed());
		$sea_level = 64;

		$top_mat = $top_mat_initial = $this->top_material->getStateId();
		$ground_mat = $ground_mat_initial = $this->ground_material->getStateId();

		$surface_height = max((int) ($surface_noise / 3.0 + 3.0 + $random->nextFloat() * 0.25), 1);
		$colored = cos($surface_noise / 3.0 * M_PI) <= 0;
		$bryce_canyon_height = 0.0;
		if($this->type === self::BRYCE){
			$noise_x = ($x & 0xFFFFFFF0) + ($z & 0xF);
			$noise_z = ($z & 0xFFFFFFF0) + ($x & 0xF);
			$noise_canyon_height = min(abs($surface_noise), $this->canyon_height_noise->noise($noise_x, $noise_z, 0, 0.5, 2.0, false));
			if($noise_canyon_height > 0){
				$heightScale = abs($this->canyon_scale_noise->noise($noise_x, $noise_z, 0, 0.5, 2.0, false));
				$bryce_canyon_height = ($noise_canyon_height ** 2) * 2.5;
				$max_height = ceil(50 * $heightScale) + 14;
				if($bryce_canyon_height > $max_height){
					$bryce_canyon_height = $max_height;
				}
				$bryce_canyon_height += $sea_level;
			}
		}

		$deep = -1;
		$ground_set = false;

		static $air = VanillaBlocks::AIR()->getStateId();
		static $stone = VanillaBlocks::STONE()->getStateId();
		static $bedrock = VanillaBlocks::BEDROCK()->getStateId();
		static $grass = VanillaBlocks::GRASS()->getStateId();
		static $coarse_dirt = VanillaBlocks::DIRT()->setDirtType(DirtType::COARSE)->getStateId();
		static $hardened_clay = VanillaBlocks::HARDENED_CLAY()->getStateId();

		static $color_states = null;
		if($color_states === null){
			$color_states = [];
			foreach(DyeColor::cases() as $color){
				$color_states[$color->name] = VanillaBlocks::STAINED_CLAY()->setColor($color)->getStateId();
			}
		}

		$color_offset = (int) round($this->color_noise->noise($x, $z, 0, 0.5, 2.0, false) * 2.0);

		$x &= Chunk::COORD_MASK;
		$z &= Chunk::COORD_MASK;

		for($y = 255; $y >= 0; --$y){
			$mat_id = $chunk->getBlockStateId($x, $y, $z);
			if($y < (int) $bryce_canyon_height && $mat_id === $air){
				$chunk->setBlockStateId($x, $y, $z, $stone);
				$mat_id = $stone;
			}
			if($y <= $random->nextBoundedInt(5)){
				$chunk->setBlockStateId($x, $y, $z, $bedrock);
			}elseif($mat_id === $air){
				$deep = -1;
			}elseif($mat_id === $stone){
				if($deep === -1){
					$ground_set = false;
					if($y >= $sea_level - 5 && $y <= $sea_level){
						$ground_mat = $ground_mat_initial;
					}

					$deep = $surface_height + max(0, $y - $sea_level - 1);
					if($y >= $sea_level - 2){
						if($this->type === self::FOREST && $y > $sea_level + 22 + ($surface_height << 1)){
							$top_mat = $colored ? $grass : $coarse_dirt;
							$chunk->setBlockStateId($x, $y, $z, $top_mat);
						}elseif($y > $sea_level + 2 + $surface_height){
							$color = $this->color_layer[($y + $color_offset) % count($this->color_layer)];
							$color = $y < $sea_level || $y > 128 ? DyeColor::ORANGE : ($colored ? $color : null);
							$chunk->setBlockStateId($x, $y, $z, $color === null ? $hardened_clay : $color_states[$color->name]);
						}else{
							$chunk->setBlockStateId($x, $y, $z, $top_mat_initial);
							$ground_set = true;
						}
					}else{
						$chunk->setBlockStateId($x, $y, $z, $ground_mat);
					}
				}elseif($deep > 0){
					--$deep;
					if($ground_set){
						$chunk->setBlockStateId($x, $y, $z, $ground_mat_initial);
					}else{
						$color = $this->color_layer[($y + $color_offset) % count($this->color_layer)];
						$chunk->setBlockStateId($x, $y, $z, $color === null ? $hardened_clay : $color_states[$color->name]);
					}
				}
			}
		}
	}

	private function setRandomLayerColor(Random $random, int $min_layer_count, int $min_layer_height, ?DyeColor $color) : void{
		for($i = 0; $i < $random->nextBoundedInt(4) + $min_layer_count; ++$i){
			$j = $random->nextBoundedInt(count($this->color_layer));
			$k = 0;
			while($k < $random->nextBoundedInt(3) + $min_layer_height && $j < count($this->color_layer)){
				$this->color_layer[$j++] = $color;
				++$k;
			}
		}
	}

	private function initializeColorLayers(Random $random) : void{
		$this->color_layer = array_fill(0, 64, null); // null = hard clay, other values are stained clay
		$i = 0;
		while($i < count($this->color_layer)){
			$i += $random->nextBoundedInt(5) + 1;
			if($i < count($this->color_layer)){
				$this->color_layer[$i++] = DyeColor::ORANGE;
			}
		}
		$this->setRandomLayerColor($random, 2, 1, DyeColor::YELLOW);
		$this->setRandomLayerColor($random, 2, 2, DyeColor::BROWN);
		$this->setRandomLayerColor($random, 2, 1, DyeColor::RED);
		$j = 0;
		for($i = 0; $i < $random->nextBoundedInt(3) + 3; ++$i){
			$j += $random->nextBoundedInt(16) + 4;
			if($j >= count($this->color_layer)){
				break;
			}
			if(($random->nextBoundedInt(2) === 0) || (($j < count($this->color_layer) - 1) && ($random->nextBoundedInt(2) === 0))){
				$this->color_layer[$j - 1] = DyeColor::LIGHT_GRAY;
			}else{
				$this->color_layer[$j] = DyeColor::WHITE;
			}
		}
	}
}