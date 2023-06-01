<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether;

use muqsit\vanillagenerator\generator\Environment;
use muqsit\vanillagenerator\generator\nether\populator\NetherPopulator;
use muqsit\vanillagenerator\generator\noise\glowstone\PerlinOctaveGenerator;
use muqsit\vanillagenerator\generator\overworld\WorldType;
use muqsit\vanillagenerator\generator\utils\NetherWorldOctaves;
use muqsit\vanillagenerator\generator\utils\preset\SimpleGeneratorPreset;
use muqsit\vanillagenerator\generator\VanillaBiomeGrid;
use muqsit\vanillagenerator\generator\VanillaGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

/**
 * @extends VanillaGenerator<NetherWorldOctaves<PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator>>
 */
class NetherGenerator extends VanillaGenerator{

	protected const COORDINATE_SCALE = 684.412;
	protected const HEIGHT_SCALE = 2053.236;
	protected const HEIGHT_NOISE_SCALE_X = 100.0;
	protected const HEIGHT_NOISE_SCALE_Z = 100.0;
	protected const DETAIL_NOISE_SCALE_X = 80.0;
	protected const DETAIL_NOISE_SCALE_Y = 60.0;
	protected const DETAIL_NOISE_SCALE_Z = 80.0;
	protected const SURFACE_SCALE = 0.0625;

	/**
	 * @param int $i 0-4
	 * @param int $j 0-4
	 * @param int $k 0-32
	 * @return int
	 */
	private static function densityHash(int $i, int $j, int $k) : int{
		return ($k << 6) | ($j << 3) | $i;
	}

	protected int $bedrock_roughness = 5;

	public function __construct(int $seed, string $preset_string){
		$preset = SimpleGeneratorPreset::parse($preset_string);
		parent::__construct(
			$seed,
			$preset->exists("environment") ? Environment::fromString($preset->getString("environment")) : Environment::NETHER,
			$preset->exists("worldtype") ? WorldType::fromString($preset->getString("worldtype")) : null,
			$preset
		);
		$this->addPopulators(new NetherPopulator($this->getMaxY())); // This isn't faithful to original code. Was $world->getWorldHeight()
	}

	public function getBedrockRoughness() : int{
		return $this->bedrock_roughness;
	}

	public function setBedrockRoughness(int $bedrock_roughness) : void{
		$this->bedrock_roughness = $bedrock_roughness;
	}

	public function getMaxY() : int{
		return 128;
	}

	protected function generateChunkData(ChunkManager $world, int $chunk_x, int $chunk_z, VanillaBiomeGrid $biomes) : void{
		$this->generateRawTerrain($world, $chunk_x, $chunk_z);
		$cx = $chunk_x << Chunk::COORD_BIT_SIZE;
		$cz = $chunk_z << Chunk::COORD_BIT_SIZE;

		$octaves = $this->getWorldOctaves();

		$surface_noise = $octaves->surface->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$soul_sand_noise = $octaves->soul_sand->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$grave_noise = $octaves->gravel->getFractalBrownianMotion($cx, 0, $cz, 0.5, 2.0);

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunk_x, $chunk_z);

		$min_y = $world->getMinY();
		$max_y = $world->getMaxY();
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$id = $biomes->getBiome($x, $z);
				for($y = $min_y; $y < $max_y; ++$y){
					$chunk->setBiomeId($x, $y, $z, $id);
				}
				$this->generateTerrainColumn($world, $cx + $x, $cz + $z, $surface_noise[$x | $z << Chunk::COORD_BIT_SIZE], $soul_sand_noise[$x | $z << Chunk::COORD_BIT_SIZE], $grave_noise[$x | $z << Chunk::COORD_BIT_SIZE]);
			}
		}
	}

	protected function createWorldOctaves() : NetherWorldOctaves{
		$seed = new Random($this->random->getSeed());

		$height = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 1, 5);
		$height->x_scale = static::HEIGHT_NOISE_SCALE_X;
		$height->z_scale = static::HEIGHT_NOISE_SCALE_Z;

		$roughness = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$roughness->x_scale = static::COORDINATE_SCALE;
		$roughness->y_scale = static::HEIGHT_SCALE;
		$roughness->z_scale = static::COORDINATE_SCALE;

		$roughness2 = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$roughness2->x_scale = static::COORDINATE_SCALE;
		$roughness2->y_scale = static::HEIGHT_SCALE;
		$roughness2->z_scale = static::COORDINATE_SCALE;

		$detail = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 8, 5, 17, 5);
		$detail->x_scale = static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_X;
		$detail->y_scale = static::HEIGHT_SCALE / static::DETAIL_NOISE_SCALE_Y;
		$detail->z_scale = static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_Z;

		$surface = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$surface->setScale(static::SURFACE_SCALE);

		$soulsand = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$soulsand->x_scale = static::SURFACE_SCALE / 2.0;
		$soulsand->y_scale = static::SURFACE_SCALE / 2.0;

		$gravel = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 1, 16);
		$gravel->x_scale = static::SURFACE_SCALE / 2.0;
		$gravel->z_scale = static::SURFACE_SCALE / 2.0;

		return new NetherWorldOctaves($height, $roughness, $roughness2, $detail, $surface, $soulsand, $gravel);
	}

	private function generateRawTerrain(ChunkManager $world, int $chunk_x, int $chunk_z) : void{
		$density = $this->generateTerrainDensity($chunk_x << 2, $chunk_z << 2);

		$nether_rack = VanillaBlocks::NETHERRACK()->getStateId();
		$still_lava = VanillaBlocks::LAVA()->getStillForm()->getStateId();

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunk_x, $chunk_z);

		for ($i = 0; $i < 5 - 1; ++$i) {
			for ($j = 0; $j < 5 - 1; ++$j) {
				for ($k = 0; $k < 17 - 1; ++$k) {
					$d1 = $density[self::densityHash($i, $j, $k)];
                    $d2 = $density[self::densityHash($i + 1, $j, $k)];
                    $d3 = $density[self::densityHash($i, $j + 1, $k)];
                    $d4 = $density[self::densityHash($i + 1, $j + 1, $k)];
                    $d5 = ($density[self::densityHash($i, $j, $k + 1)] - $d1) / 8;
                    $d6 = ($density[self::densityHash($i + 1, $j, $k + 1)] - $d2) / 8;
                    $d7 = ($density[self::densityHash($i, $j + 1, $k + 1)] - $d3) / 8;
                    $d8 = ($density[self::densityHash($i + 1, $j + 1, $k + 1)] - $d4) / 8;

                    for ($l = 0; $l < 8; ++$l) {
						$d9 = $d1;
                        $d10 = $d3;

						$y_pos = $l + ($k << 3);
						$y_block_pos = $y_pos & 0xf;
						$sub_chunk = $chunk->getSubChunk($y_pos >> Chunk::COORD_BIT_SIZE);

                        for ($m = 0; $m < 4; ++$m) {
							$dens = $d9;
                            for ($n = 0; $n < 4; ++$n) {
								// any density higher than 0 is ground, any density lower or equal
								// to 0 is air (or lava if under the lava level).
								if ($dens > 0) {
									$sub_chunk->setBlockStateId($m + ($i << 2), $y_block_pos, $n + ($j << 2), $nether_rack);
								} else if ($l + ($k << 3) < 32) {
									$sub_chunk->setBlockStateId($m + ($i << 2), $y_block_pos, $n + ($j << 2), $still_lava);
								}
								// interpolation along z
								$dens += ($d10 - $d9) / 4;
							}
                            // interpolation along x
                            $d9 += ($d2 - $d1) / 4;
                            // interpolate along z
                            $d10 += ($d4 - $d3) / 4;
                        }
                        // interpolation along y
                        $d1 += $d5;
                        $d3 += $d7;
                        $d2 += $d6;
                        $d4 += $d8;
                    }
                }
            }
        }
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @return float[]
	 */
	private function generateTerrainDensity(int $x, int $z) : array{
		$octaves = $this->getWorldOctaves();
		$height_noise = $octaves->height->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughness_noise = $octaves->roughness->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughness_noise_2 = $octaves->roughness_2->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$detail_noise = $octaves->detail->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);

		$k_max = $octaves->detail->size_y;

		static $nv = null;
		if($nv === null){
			$nv = [];
			for($i = 0; $i < $k_max; ++$i){
				$nv[$i] = cos($i * M_PI * 6.0 / $k_max) * 2.0;
				$nh = $i > $k_max / 2 ? $k_max - 1 - $i : $i;
				if($nh < 4.0){
					$nh = 4.0 - $nh;
					$nv[$i] -= $nh * $nh * $nh * 10.0;
				}
			}
		}

		$index = 0;
		$index_height = 0;

		$density = [];

		for($i = 0; $i < 5; ++$i){
			for($j = 0; $j < 5; ++$j){

				$noise_h = $height_noise[$index_height++] / 8000.0;
				if($noise_h < 0){
					$noise_h = -$noise_h;
				}
				$noise_h = $noise_h * 3.0 - 3.0;
				if($noise_h < 0){
					$noise_h = max($noise_h * 0.5, -1) / 1.4 * 0.5;
				}else{
					$noise_h = min($noise_h, 1) / 6.0;
				}

				$noise_h = $noise_h * $k_max / 16.0;
				for($k = 0; $k < $k_max; ++$k){
					$noise_r = $roughness_noise[$index] / 512.0;
					$noise_r_2 = $roughness_noise_2[$index] / 512.0;
					$noise_d = ($detail_noise[$index] / 10.0 + 1.0) / 2.0;
					$nh = $nv[$k];
					// linear interpolation
					$dens = $noise_d < 0 ? $noise_r : ($noise_d > 1 ? $noise_r_2 : $noise_r + ($noise_r_2 - $noise_r) * $noise_d);
					$dens -= $nh;
					++$index;
					$k_cap = $k_max - 4;
					if($k > $k_cap){
						$lowering = ($k - $k_cap) / 3.0;
						$dens = $dens * (1.0 - $lowering) + $lowering * -10.0;
					}
					$density[self::densityHash($i, $j, $k)] = $dens;
				}
			}
		}

		return $density;
	}

	public function generateTerrainColumn(ChunkManager $world, int $x, int $z, float $surface_noise, float $soul_sand_noise, float $grave_noise) : void{
		$soul_sand = $soul_sand_noise + $this->random->nextFloat() * 0.2 > 0;
		$gravel = $grave_noise + $this->random->nextFloat() * 0.2 > 0;

		$surface_height = (int) ($surface_noise / 3.0 + 3.0 + $this->random->nextFloat() * 0.25);
		$deep = -1;
		$world_height = $this->getMaxY();
		$world_height_m1 = $world_height - 1;

		$block_bedrock = VanillaBlocks::BEDROCK()->getStateId();
		$block_air = VanillaBlocks::AIR()->getStateId();
		$block_nether_rack = VanillaBlocks::NETHERRACK()->getStateId();
		$block_gravel = VanillaBlocks::GRAVEL()->getStateId();
		$block_soul_sand = VanillaBlocks::SOUL_SAND()->getStateId();

		$top_mat = $block_nether_rack;
		$ground_mat = $block_nether_rack;

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($x >> Chunk::COORD_BIT_SIZE, $z >> Chunk::COORD_BIT_SIZE);
		$chunk_block_x = $x & Chunk::COORD_MASK;
		$chunk_block_z = $z & Chunk::COORD_MASK;

		for($y = $world_height_m1; $y >= 0; --$y){
			if($y <= $this->random->nextBoundedInt($this->bedrock_roughness) || $y >= $world_height_m1 - $this->random->nextBoundedInt($this->bedrock_roughness)){
				$chunk->setBlockStateId($chunk_block_x, $y, $chunk_block_z, $block_bedrock);
				continue;
			}
			$mat = $chunk->getBlockStateId($chunk_block_x, $y, $chunk_block_z);
			if($mat === $block_air){
				$deep = -1;
			}elseif($mat === $block_nether_rack){
				if($deep === -1){
					if($surface_height <= 0){
						$top_mat = $block_air;
						$ground_mat = $block_nether_rack;
					}elseif($y >= 60 && $y <= 65){
						$top_mat = $block_nether_rack;
						$ground_mat = $block_nether_rack;
						if($gravel){
							$top_mat = $block_gravel;
							$ground_mat = $block_nether_rack;
						}
						if($soul_sand){
							$top_mat = $block_soul_sand;
							$ground_mat = $block_soul_sand;
						}
					}

					$deep = $surface_height;
					if($y >= 63){
						$chunk->setBlockStateId($chunk_block_x, $y, $chunk_block_z, $top_mat);
					}else{
						$chunk->setBlockStateId($chunk_block_x, $y, $chunk_block_z, $ground_mat);
					}
				}elseif($deep > 0){
					--$deep;
					$chunk->setBlockStateId($chunk_block_x, $y, $chunk_block_z, $ground_mat);
				}
			}
		}
	}
}