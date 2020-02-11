<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether;

use muqsit\vanillagenerator\generator\nether\populator\NetherPopulator;
use muqsit\vanillagenerator\generator\noise\glowstone\PerlinOctaveGenerator;
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

	/** @@var float[][][] */
	private $density = [];

	public function __construct(ChunkManager $world, int $seed, array $options = []){
		parent::__construct($world, $seed, $options);
		$this->addPopulators(new NetherPopulator($world->getWorldHeight()));
	}

	public function getWorldHeight() : int{
		return 128;
	}

	protected function generateChunkData(int $chunkX, int $chunkZ, VanillaBiomeGrid $biomes) : void{
		$this->generateRawTerrain($chunkX, $chunkZ);
		$cx = $chunkX << 4;
		$cz = $chunkZ << 4;

		$surfaceNoise = $this->getWorldOctaves()["surface"]->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$soulsandNoise = $this->getWorldOctaves()["soulsand"]->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$gravelNoise = $this->getWorldOctaves()["gravel"]->getFractalBrownianMotion($cx, 0, $cz, 0.5, 2.0);

		/** @var Chunk $chunk */
		$chunk = $this->world->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$chunk->setBiomeId($x, $z, $id = $biomes->getBiome($x, $z));
				$this->generateTerrainColumn($cx + $x, $cz + $z, $surfaceNoise[$x | $z << 4], $soulsandNoise[$x | $z << 4], $gravelNoise[$x | $z << 4]);
			}
		}
	}

	protected function createWorldOctaves(array &$octaves) : void{
		$seed = new Random($this->random->getSeed());

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 1, 5);
		$gen->setXScale(static::HEIGHT_NOISE_SCALE_X);
		$gen->setZScale(static::HEIGHT_NOISE_SCALE_Z);
		$octaves["height"] = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$gen->setXScale(static::COORDINATE_SCALE);
		$gen->setYScale(static::HEIGHT_SCALE);
		$gen->setZScale(static::COORDINATE_SCALE);
		$octaves["roughness"] = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$gen->setXScale(static::COORDINATE_SCALE);
		$gen->setYScale(static::HEIGHT_SCALE);
		$gen->setZScale(static::COORDINATE_SCALE);
		$octaves["roughness2"] = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 8, 5, 17, 5);
		$gen->setXScale(static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_X);
		$gen->setYScale(static::HEIGHT_SCALE / static::DETAIL_NOISE_SCALE_Y);
		$gen->setZScale(static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_Z);
		$octaves["detail"] = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$gen->setScale(static::SURFACE_SCALE);
		$octaves["surface"] = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$gen->setXScale(static::SURFACE_SCALE / 2.0);
		$gen->setYScale(static::SURFACE_SCALE / 2.0);
		$octaves["soulsand"] = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 1, 16);
		$gen->setXScale(static::SURFACE_SCALE / 2.0);
		$gen->setZScale(static::SURFACE_SCALE / 2.0);
		$octaves["gravel"] = $gen;
	}

	private function generateRawTerrain(int $chunkX, int $chunkZ) : void{
		$this->generateTerrainDensity($chunkX << 2, $chunkZ << 2);

		$x = $chunkX << 4;
		$z = $chunkZ << 4;

		for ($i = 0; $i < 5 - 1; ++$i) {
			for ($j = 0; $j < 5 - 1; ++$j) {
				for ($k = 0; $k < 17 - 1; ++$k) {
					$d1 = $this->density[$i][$j][$k];
                    $d2 = $this->density[$i + 1][$j][$k];
                    $d3 = $this->density[$i][$j + 1][$k];
                    $d4 = $this->density[$i + 1][$j + 1][$k];
                    $d5 = ($this->density[$i][$j][$k + 1] - $d1) / 8;
                    $d6 = ($this->density[$i + 1][$j][$k + 1] - $d2) / 8;
                    $d7 = ($this->density[$i][$j + 1][$k + 1] - $d3) / 8;
                    $d8 = ($this->density[$i + 1][$j + 1][$k + 1] - $d4) / 8;

                    for ($l = 0; $l < 8; ++$l) {
						$d9 = $d1;
                        $d10 = $d3;
                        for ($m = 0; $m < 4; ++$m) {
							$dens = $d9;
                            for ($n = 0; $n < 4; ++$n) {
								// any density higher than 0 is ground, any density lower or equal
								// to 0 is air (or lava if under the lava level).
								if ($dens > 0) {
									$this->world->setBlockAt($x + $m + ($i << 2), $l + ($k << 3), $z + $n + ($j << 2), VanillaBlocks::NETHERRACK());
								} else if ($l + ($k << 3) < 32) {
									$this->world->setBlockAt($x + $m + ($i << 2), $l + ($k << 3), $z + $n + ($j << 2), BlockFactory::get(BlockLegacyIds::STILL_LAVA));
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

	private function generateTerrainDensity(int $x, int $z) : void{
		/** @var PerlinOctaveGenerator[] $octaves */
		$octaves = $this->getWorldOctaves();
		$heightNoise = $octaves["height"]->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughnessNoise = $octaves["roughness"]->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughnessNoise2 = $octaves["roughness2"]->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$detailNoise = $octaves["detail"]->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);

		$k_max = $octaves["detail"]->getSizeY();

		$nv = [];
		for($i = 0; $i < $k_max; ++$i){
			$nv[$i] = cos($i * M_PI * 6.0 / $k_max) * 2.0;
			$nh = $i > $k_max / 2 ? $k_max - 1 - $i : $i;
			if($nh < 4.0){
				$nh = 4.0 - $nh;
				$nv[$i] -= $nh * $nh * $nh * 10.0;
			}
		}

		$index = 0;
		$indexHeight = 0;

		for($i = 0; $i < 5; ++$i){
			for($j = 0; $j < 5; ++$j){

				$noiseH = $heightNoise[$indexHeight++] / 8000.0;
				if($noiseH < 0){
					$noiseH = abs($noiseH);
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
					$this->density[$i][$j][$k] = $dens;
				}
			}
		}
	}

	public function generateTerrainColumn(int $x, int $z, float $surfaceNoise, float $soulsandNoise, float $gravelNoise) : void{
		$topMat = VanillaBlocks::NETHERRACK();
		$groundMat = VanillaBlocks::NETHERRACK();

		$soulSand = $soulsandNoise + $this->random->nextFloat() * 0.2 > 0;
		$gravel = $gravelNoise + $this->random->nextFloat() * 0.2 > 0;

		$surfaceHeight = (int) ($surfaceNoise / 3.0 + 3.0 + $this->random->nextFloat() * 0.25);
		$deep = -1;
		$worldHeight = $this->getWorldHeight();
		$worldHeightM1 = $worldHeight - 1;
		for($y = $worldHeightM1; $y >= 0; --$y){
			if($y <= $this->random->nextBoundedInt(5) || $y >= $worldHeightM1 - $this->random->nextBoundedInt(5)){
				$this->world->setBlockAt($x, $y, $z, VanillaBlocks::BEDROCK());
				continue;
			}
			$mat = $this->world->getBlockAt($x, $y, $z)->getId();
			if($mat === BlockLegacyIds::AIR){
				$deep = -1;
			}elseif($mat === BlockLegacyIds::NETHERRACK){
				if($deep === -1){
					if($surfaceHeight <= 0){
						$topMat = VanillaBlocks::AIR();
						$groundMat = VanillaBlocks::NETHERRACK();
					}elseif($y >= 60 && $y <= 65){
						$topMat = VanillaBlocks::NETHERRACK();
						$groundMat = VanillaBlocks::NETHERRACK();
						if($gravel){
							$topMat = VanillaBlocks::GRAVEL();
							$groundMat = VanillaBlocks::NETHERRACK();
						}
						if($soulSand){
							$topMat = VanillaBlocks::SOUL_SAND();
							$groundMat = VanillaBlocks::SOUL_SAND();
						}
					}

					$deep = $surfaceHeight;
					if($y >= 63){
						$this->world->setBlockAt($x, $y, $z, $topMat);
					}else{
						$this->world->setBlockAt($x, $y, $z, $groundMat);
					}
				}elseif($deep > 0){
					--$deep;
					$this->world->setBlockAt($x, $y, $z, $groundMat);
				}
			}
		}
	}
}