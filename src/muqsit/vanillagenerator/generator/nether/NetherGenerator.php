<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether;

use muqsit\vanillagenerator\generator\Environment;
use muqsit\vanillagenerator\generator\nether\populator\NetherPopulator;
use muqsit\vanillagenerator\generator\noise\glowstone\PerlinOctaveGenerator;
use muqsit\vanillagenerator\generator\utils\NetherWorldOctaves;
use muqsit\vanillagenerator\generator\VanillaBiomeGrid;
use muqsit\vanillagenerator\generator\VanillaGenerator;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

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

	/** @var int */
	protected $bedrock_roughness = 5;

	public function __construct(int $seed, array $options = []){
		parent::__construct($seed, Environment::NETHER, null, $options);
		$this->addPopulators(new NetherPopulator($this->getWorldHeight())); // This isn't faithful to original code. Was $world->getWorldHeight()
	}

	public function getBedrockRoughness() : int{
		return $this->bedrock_roughness;
	}

	public function setBedrockRoughness(int $bedrock_roughness) : void{
		$this->bedrock_roughness = $bedrock_roughness;
	}

	public function getWorldHeight() : int{
		return 128;
	}

	protected function generateChunkData(ChunkManager $world, int $chunkX, int $chunkZ, VanillaBiomeGrid $biomes) : void{
		$this->generateRawTerrain($world, $chunkX, $chunkZ);
		$cx = $chunkX << 4;
		$cz = $chunkZ << 4;

		/** @var NetherWorldOctaves $octaves */
		$octaves = $this->getWorldOctaves();

		$surfaceNoise = $octaves->surface->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$soulsandNoise = $octaves->soul_sand->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$gravelNoise = $octaves->gravel->getFractalBrownianMotion($cx, 0, $cz, 0.5, 2.0);

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$chunk->setBiomeId($x, $z, $id = $biomes->getBiome($x, $z));
				$this->generateTerrainColumn($world, $cx + $x, $cz + $z, $surfaceNoise[$x | $z << 4], $soulsandNoise[$x | $z << 4], $gravelNoise[$x | $z << 4]);
			}
		}
	}

	protected function createWorldOctaves() : NetherWorldOctaves{
		$seed = new Random($this->random->getSeed());

		$height = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 1, 5);
		$height->setXScale(static::HEIGHT_NOISE_SCALE_X);
		$height->setZScale(static::HEIGHT_NOISE_SCALE_Z);

		$roughness = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$roughness->setXScale(static::COORDINATE_SCALE);
		$roughness->setYScale(static::HEIGHT_SCALE);
		$roughness->setZScale(static::COORDINATE_SCALE);

		$roughness2 = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$roughness2->setXScale(static::COORDINATE_SCALE);
		$roughness2->setYScale(static::HEIGHT_SCALE);
		$roughness2->setZScale(static::COORDINATE_SCALE);

		$detail = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 8, 5, 17, 5);
		$detail->setXScale(static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_X);
		$detail->setYScale(static::HEIGHT_SCALE / static::DETAIL_NOISE_SCALE_Y);
		$detail->setZScale(static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_Z);

		$surface = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$surface->setScale(static::SURFACE_SCALE);

		$soulsand = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$soulsand->setXScale(static::SURFACE_SCALE / 2.0);
		$soulsand->setYScale(static::SURFACE_SCALE / 2.0);

		$gravel = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 1, 16);
		$gravel->setXScale(static::SURFACE_SCALE / 2.0);
		$gravel->setZScale(static::SURFACE_SCALE / 2.0);

		return new NetherWorldOctaves($height, $roughness, $roughness2, $detail, $surface, $soulsand, $gravel);
	}

	private function generateRawTerrain(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$density = $this->generateTerrainDensity($chunkX << 2, $chunkZ << 2);

		$nether_rack = VanillaBlocks::NETHERRACK()->getFullId();
		$still_lava = BlockFactory::getInstance()->get(BlockLegacyIds::STILL_LAVA)->getFullId();

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunkX, $chunkZ);

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
						$subChunk = $chunk->getSubChunk($y_pos >> 4);

                        for ($m = 0; $m < 4; ++$m) {
							$dens = $d9;
                            for ($n = 0; $n < 4; ++$n) {
								// any density higher than 0 is ground, any density lower or equal
								// to 0 is air (or lava if under the lava level).
								if ($dens > 0) {
									$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $nether_rack);
								} else if ($l + ($k << 3) < 32) {
									$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $still_lava);
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
		/** @var NetherWorldOctaves $octaves */
		$octaves = $this->getWorldOctaves();
		$heightNoise = $octaves->height->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughnessNoise = $octaves->roughness->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughnessNoise2 = $octaves->roughness2->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$detailNoise = $octaves->detail->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);

		$k_max = $octaves->detail->getSizeY();

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
		$indexHeight = 0;

		$density = [];

		for($i = 0; $i < 5; ++$i){
			for($j = 0; $j < 5; ++$j){

				$noiseH = $heightNoise[$indexHeight++] / 8000.0;
				if($noiseH < 0){
					$noiseH = -$noiseH;
				}
				$noiseH = $noiseH * 3.0 - 3.0;
				if($noiseH < 0){
					$noiseH = max($noiseH * 0.5, -1) / 1.4 * 0.5;
				}else{
					$noiseH = min($noiseH, 1) / 6.0;
				}

				$noiseH = $noiseH * $k_max / 16.0;
				for($k = 0; $k < $k_max; ++$k){
					$noiseR = $roughnessNoise[$index] / 512.0;
					$noiseR2 = $roughnessNoise2[$index] / 512.0;
					$noiseD = ($detailNoise[$index] / 10.0 + 1.0) / 2.0;
					$nh = $nv[$k];
					// linear interpolation
					$dens = $noiseD < 0 ? $noiseR : ($noiseD > 1 ? $noiseR2 : $noiseR + ($noiseR2 - $noiseR) * $noiseD);
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

	public function generateTerrainColumn(ChunkManager $world, int $x, int $z, float $surfaceNoise, float $soulsandNoise, float $gravelNoise) : void{
		$soulSand = $soulsandNoise + $this->random->nextFloat() * 0.2 > 0;
		$gravel = $gravelNoise + $this->random->nextFloat() * 0.2 > 0;

		$surfaceHeight = (int) ($surfaceNoise / 3.0 + 3.0 + $this->random->nextFloat() * 0.25);
		$deep = -1;
		$worldHeight = $this->getWorldHeight();
		$worldHeightM1 = $worldHeight - 1;

		$block_bedrock = VanillaBlocks::BEDROCK()->getFullId();
		$block_air = VanillaBlocks::AIR()->getFullId();
		$block_nether_rack = VanillaBlocks::NETHERRACK()->getFullId();
		$block_gravel = VanillaBlocks::GRAVEL()->getFullId();
		$block_soul_sand = VanillaBlocks::SOUL_SAND()->getFullId();

		$topMat = $block_nether_rack;
		$groundMat = $block_nether_rack;

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($x >> 4, $z >> 4);
		$chunk_block_x = $x & 0x0f;
		$chunk_block_z = $z & 0x0f;

		for($y = $worldHeightM1; $y >= 0; --$y){
			if($y <= $this->random->nextBoundedInt($this->bedrock_roughness) || $y >= $worldHeightM1 - $this->random->nextBoundedInt($this->bedrock_roughness)){
				$chunk->setFullBlock($chunk_block_x, $y, $chunk_block_z, $block_bedrock);
				continue;
			}
			$mat = $chunk->getFullBlock($chunk_block_x, $y, $chunk_block_z);
			if($mat === $block_air){
				$deep = -1;
			}elseif($mat === $block_nether_rack){
				if($deep === -1){
					if($surfaceHeight <= 0){
						$topMat = $block_air;
						$groundMat = $block_nether_rack;
					}elseif($y >= 60 && $y <= 65){
						$topMat = $block_nether_rack;
						$groundMat = $block_nether_rack;
						if($gravel){
							$topMat = $block_gravel;
							$groundMat = $block_nether_rack;
						}
						if($soulSand){
							$topMat = $block_soul_sand;
							$groundMat = $block_soul_sand;
						}
					}

					$deep = $surfaceHeight;
					if($y >= 63){
						$chunk->setFullBlock($chunk_block_x, $y, $chunk_block_z, $topMat);
					}else{
						$chunk->setFullBlock($chunk_block_x, $y, $chunk_block_z, $groundMat);
					}
				}elseif($deep > 0){
					--$deep;
					$chunk->setFullBlock($chunk_block_x, $y, $chunk_block_z, $groundMat);
				}
			}
		}
	}
}