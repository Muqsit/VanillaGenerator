<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld;

use muqsit\vanillagenerator\generator\ground\DirtAndStonePatchGroundGenerator;
use muqsit\vanillagenerator\generator\ground\DirtPatchGroundGenerator;
use muqsit\vanillagenerator\generator\ground\GravelPatchGroundGenerator;
use muqsit\vanillagenerator\generator\ground\GroundGenerator;
use muqsit\vanillagenerator\generator\ground\MesaGroundGenerator;
use muqsit\vanillagenerator\generator\ground\MycelGroundGenerator;
use muqsit\vanillagenerator\generator\ground\RockyGroundGenerator;
use muqsit\vanillagenerator\generator\ground\SandyGroundGenerator;
use muqsit\vanillagenerator\generator\ground\SnowyGroundGenerator;
use muqsit\vanillagenerator\generator\ground\StonePatchGroundGenerator;
use muqsit\vanillagenerator\generator\noise\glowstone\PerlinOctaveGenerator;
use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeHeightManager;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\populator\OverworldPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\SnowPopulator;
use muqsit\vanillagenerator\generator\utils\WorldOctaves;
use muqsit\vanillagenerator\generator\VanillaBiomeGrid;
use muqsit\vanillagenerator\generator\VanillaGenerator;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class OverworldGenerator extends VanillaGenerator{

	/** @var float[] */
	private static $ELEVATION_WEIGHT = [];

	/** @var GroundGenerator[] */
	private static $GROUND_MAP = [];

	/**
	 * @param int $x 0-4
	 * @param int $z 0-4
	 * @return int
	 */
	private static function elevationWeightHash(int $x, int $z) : int{
		return ($x << 3) | $z;
	}

	/**
	 * @param int $i 0-4
	 * @param int $j 0-4
	 * @param int $k 0-32
	 * @return int
	 */
	private static function densityHash(int $i, int $j, int $k) : int{
		return ($k << 6) | ($j << 3) | $i;
	}

	public static function init() : void{
		self::setBiomeSpecificGround(new SandyGroundGenerator(), BiomeIds::BEACH, BiomeIds::COLD_BEACH, BiomeIds::DESERT, BiomeIds::DESERT_HILLS, BiomeIds::MUTATED_DESERT);
		self::setBiomeSpecificGround(new RockyGroundGenerator(), BiomeIds::STONE_BEACH);
		self::setBiomeSpecificGround(new SnowyGroundGenerator(), BiomeIds::MUTATED_ICE_FLATS);
		self::setBiomeSpecificGround(new MycelGroundGenerator(), BiomeIds::MUSHROOM_ISLAND, BiomeIds::MUSHROOM_ISLAND_SHORE);
		self::setBiomeSpecificGround(new StonePatchGroundGenerator(), BiomeIds::EXTREME_HILLS);
		self::setBiomeSpecificGround(new GravelPatchGroundGenerator(), BiomeIds::MUTATED_EXTREME_HILLS, BiomeIds::MUTATED_EXTREME_HILLS_WITH_TREES);
		self::setBiomeSpecificGround(new DirtAndStonePatchGroundGenerator(), BiomeIds::MUTATED_SAVANNA, BiomeIds::MUTATED_SAVANNA_ROCK);
		self::setBiomeSpecificGround(new DirtPatchGroundGenerator(), BiomeIds::REDWOOD_TAIGA, BiomeIds::REDWOOD_TAIGA_HILLS, BiomeIds::MUTATED_REDWOOD_TAIGA, BiomeIds::MUTATED_REDWOOD_TAIGA_HILLS);
		self::setBiomeSpecificGround(new MesaGroundGenerator(), BiomeIds::MESA, BiomeIds::MESA_CLEAR_ROCK, BiomeIds::MESA_ROCK);
		self::setBiomeSpecificGround(new MesaGroundGenerator(MesaGroundGenerator::BRYCE), BiomeIds::MUTATED_MESA);
		self::setBiomeSpecificGround(new MesaGroundGenerator(MesaGroundGenerator::FOREST), BiomeIds::MESA_ROCK, BiomeIds::MUTATED_MESA_ROCK);

		// fill a 5x5 array with values that acts as elevation weight on chunk neighboring,
		// this can be viewed as a parabolic field: the center gets the more weight, and the
		// weight decreases as distance increases from the center. This is applied on the
		// lower scale biome grid.
		for($x = 0; $x < 5; ++$x){
			for($z = 0; $z < 5; ++$z){
				$sqX = $x - 2;
				$sqX *= $sqX;
				$sqZ = $z - 2;
				$sqZ *= $sqZ;
				self::$ELEVATION_WEIGHT[self::elevationWeightHash($x, $z)] = 10.0 / sqrt($sqX + $sqZ + 0.2);
			}
		}
	}

	private static function setBiomeSpecificGround(GroundGenerator $gen, int ...$biomes) : void{
		foreach($biomes as $biome){
			self::$GROUND_MAP[$biome] = $gen;
		}
	}

	protected const COORDINATE_SCALE = 684.412;
	protected const HEIGHT_SCALE = 684.412;
	protected const HEIGHT_NOISE_SCALE_X = 200.0;
	protected const HEIGHT_NOISE_SCALE_Z = 200.0;
	protected const DETAIL_NOISE_SCALE_X = 80.0;
	protected const DETAIL_NOISE_SCALE_Y = 160.0;
	protected const DETAIL_NOISE_SCALE_Z = 80.0;
	protected const SURFACE_SCALE = 0.0625;
	protected const BASE_SIZE = 8.5;
	protected const STRETCH_Y = 12.0;
	protected const BIOME_HEIGHT_OFFSET = 0.0;
	protected const BIOME_HEIGHT_WEIGHT = 1.0;
	protected const BIOME_SCALE_OFFSET = 0.0;
	protected const BIOME_SCALE_WEIGHT = 1.0;
	protected const DENSITY_FILL_MODE = 0;
	protected const DENSITY_FILL_SEA_MODE = 0;
	protected const DENSITY_FILL_OFFSET = 0.0;

	/** @var GroundGenerator */
	private $groundGen;

	/** @var string */
	private $type = WorldType::NORMAL;

	public function __construct(ChunkManager $world, int $seed, array $options = []){
		parent::__construct($world, $seed, $options);
		$this->groundGen = new GroundGenerator();
		$this->addPopulators(new OverworldPopulator(), new SnowPopulator());
	}

	protected function generateChunkData(int $chunkX, int $chunkZ, VanillaBiomeGrid $grid) : void{
		$this->generateRawTerrain($chunkX, $chunkZ);

		$cx = $chunkX << 4;
		$cz = $chunkZ << 4;

		/** @var SimplexOctaveGenerator $octaveGenerator */
		$octaveGenerator = $this->getWorldOctaves()->surface;
		$sizeX = $octaveGenerator->getSizeX();
		$sizeZ = $octaveGenerator->getSizeZ();

		$surfaceNoise = $octaveGenerator->getFractalBrownianMotion($cx, 0.0, $cz, 0.5, 0.5);

		/** @var Chunk $chunk */
		$chunk = $this->world->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < $sizeX; ++$x){
			for($z = 0; $z < $sizeZ; ++$z){
				$chunk->setBiomeId($x, $z, $id = $grid->getBiome($x, $z));
				if(isset(self::$GROUND_MAP[$id])){
					self::$GROUND_MAP[$id]->generateTerrainColumn($this->world, $this->random, $cx + $x, $cz + $z, $id, $surfaceNoise[$x | $z << 4]);
				}else{
					$this->groundGen->generateTerrainColumn($this->world, $this->random, $cx + $x, $cz + $z, $id, $surfaceNoise[$x | $z << 4]);
				}
			}
		}
	}

	protected function createWorldOctaves() : WorldOctaves{
		$seed = new Random($this->random->getSeed());

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 1, 5);
		$gen->setXScale(self::HEIGHT_NOISE_SCALE_X);
		$gen->setZScale(self::HEIGHT_NOISE_SCALE_Z);
		$height = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 33, 5);
		$gen->setXScale(self::COORDINATE_SCALE);
		$gen->setYScale(self::HEIGHT_SCALE);
		$gen->setZScale(self::COORDINATE_SCALE);
		$roughness = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 33, 5);
		$gen->setXScale(self::COORDINATE_SCALE);
		$gen->setYScale(self::HEIGHT_SCALE);
		$gen->setZScale(self::COORDINATE_SCALE);
		$roughness2 = $gen;

		$gen = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 8, 5, 33, 5);
		$gen->setXScale(self::COORDINATE_SCALE / self::DETAIL_NOISE_SCALE_X);
		$gen->setYScale(self::HEIGHT_SCALE / self::DETAIL_NOISE_SCALE_Y);
		$gen->setZScale(self::COORDINATE_SCALE / self::DETAIL_NOISE_SCALE_Z);
		$detail = $gen;

		$gen = SimplexOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 1, 16);
		$gen->setScale(self::SURFACE_SCALE);
		$surface = $gen;

		return new WorldOctaves($height, $roughness, $roughness2, $detail, $surface);
	}

	private function generateRawTerrain(int $chunkX, int $chunkZ) : void{
		$density = $this->generateTerrainDensity($chunkX, $chunkZ);

		$seaLevel = 64;

		// Terrain densities are sampled at different resolutions (1/4x on x,z and 1/8x on y by
		// default)
		// so it's needed to re-scale it. Linear interpolation is used to fill in the gaps.

		$fill = self::DENSITY_FILL_MODE;
		$afill = abs($fill);
		$seaFill = self::DENSITY_FILL_SEA_MODE;
		$densityOffset = self::DENSITY_FILL_OFFSET;

		$still_water = BlockFactory::getInstance()->get(BlockLegacyIds::STILL_WATER)->getFullId();
		$water = VanillaBlocks::WATER()->getFullId();
		$stone = VanillaBlocks::STONE()->getFullId();

		/** @var Chunk $chunk */
		$chunk = $this->world->getChunk($chunkX, $chunkZ);

		for($i = 0; $i < 5 - 1; ++$i){
			for($j = 0; $j < 5 - 1; ++$j){
				for($k = 0; $k < 33 - 1; ++$k){
					// 2x2 grid
					$d1 = $density[self::densityHash($i, $j, $k)];
					$d2 = $density[self::densityHash($i + 1, $j, $k)];
					$d3 = $density[self::densityHash($i, $j + 1, $k)];
					$d4 = $density[self::densityHash($i + 1, $j + 1, $k)];
					// 2x2 grid (row above)
					$d5 = ($density[self::densityHash($i, $j, $k + 1)] - $d1) / 8;
					$d6 = ($density[self::densityHash($i + 1, $j, $k + 1)] - $d2) / 8;
					$d7 = ($density[self::densityHash($i, $j + 1, $k + 1)] - $d3) / 8;
					$d8 = ($density[self::densityHash($i + 1, $j + 1, $k + 1)] - $d4) / 8;
					for($l = 0; $l < 8; ++$l){
						$d9 = $d1;
						$d10 = $d3;

						$y_pos = $l + ($k << 3);
						$y_block_pos = $y_pos & 0xf;
						$subChunk = $chunk->getSubChunk($y_pos >> 4);

						for($m = 0; $m < 4; ++$m){
							$dens = $d9;
							for($n = 0; $n < 4; ++$n){
								// any density higher than density offset is ground, any density
								// lower or equal to the density offset is air
								// (or water if under the sea level).
								// this can be flipped if the mode is negative, so lower or equal
								// to is ground, and higher is air/water
								// and, then data can be shifted by afill the order is air by
								// default, ground, then water. they can shift places
								// within each if statement
								// the target is densityOffset + 0, since the default target is
								// 0, so don't get too confused by the naming :)
								if($afill === 1 || $afill === 10 || $afill === 13 || $afill === 16){
									$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $water);
								}elseif($afill === 2 || $afill === 9 || $afill === 12 || $afill === 15){
									$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $stone);
								}

								if(($dens > $densityOffset && $fill > -1) || ($dens <= $densityOffset && $fill < 0)){
									if($afill === 0 || $afill === 3 || $afill === 6 || $afill === 9 || $afill === 12){
										$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $stone);
									}elseif($afill === 2 || $afill === 7 || $afill === 10 || $afill === 16){
										$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $still_water);
									}
								}elseif(($y_pos < $seaLevel - 1 && $seaFill === 0) || ($y_pos >= $seaLevel - 1 && $seaFill === 1)){
									if($afill === 0 || $afill === 3 || $afill === 7 || $afill === 10 || $afill === 13){
										$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $still_water);
									}elseif($afill === 1 || $afill === 6 || $afill === 9 || $afill === 15){
										$subChunk->setFullBlock($m + ($i << 2), $y_block_pos, $n + ($j << 2), $stone);
									}
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
		$density = [];

		// Scaling chunk x and z coordinates (4x, see below)
		$x <<= 2;
		$z <<= 2;

		// Get biome grid data at lower res (scaled 4x, at this scale a chunk is 4x4 columns of
		// the biome grid),
		// we are loosing biome detail but saving huge amount of computation.
		// We need 1 chunk (4 columns) + 1 column for later needed outer edges (1 column) and at
		// least 2 columns
		// on each side to be able to cover every value.
		// 4 + 1 + 2 + 2 = 9 columns but the biomegrid generator needs a multiple of 2 so we ask
		// 10 columns wide
		// to the biomegrid generator.
		// This gives a total of 81 biome grid columns to work with, and this includes the chunk
		// neighborhood.
		$biomeGrid = $this->getBiomeGridAtLowerRes($x - 2, $z - 2, 10, 10);

		$octaves = $this->getWorldOctaves();
		$heightNoise = $octaves->height->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughnessNoise = $octaves->roughness->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughnessNoise2 = $octaves->roughness2->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$detailNoise = $octaves->detail->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);

		$index = 0;
		$indexHeight = 0;

		// Sampling densities.
		// Ideally we would sample 512 (4x4x32) values but in reality we need 825 values (5x5x33).
		// This is because linear interpolation is done later to re-scale so we need right and
		// bottom edge values if we want it to be "seamless".
		// You can check this picture to have a visualization of how the biomegrid is traversed
		// (2D plan):
		// http://i.imgur.com/s4whlZE.png
		// The big square grid represents our lower res biomegrid columns, and the very small
		// square grid
		// represents the normal biome grid columns (at block level) and the reason why it's
		// required to
		// re-scale it and do linear interpolation before densities can be used to generate raw
		// terrain.
		for($i = 0; $i < 5; ++$i){
			for($j = 0; $j < 5; ++$j){
				$avgHeightScale = 0.0;
				$avgHeightBase = 0.0;
				$totalWeight = 0.0;
				$biome = $biomeGrid[$i + 2 + ($j + 2) * 10];
				$biomeHeight = BiomeHeightManager::get($biome);
				// Sampling an average height base and scale by visiting the neighborhood
				// of the current biomegrid column.
				for($m = 0; $m < 5; ++$m){
					for($n = 0; $n < 5; ++$n){
						$nearBiome = $biomeGrid[$i + $m + ($j + $n) * 10];
						$nearBiomeHeight = BiomeHeightManager::get($nearBiome);
						$heightBase = self::BIOME_HEIGHT_OFFSET + $nearBiomeHeight->getHeight() * self::BIOME_HEIGHT_WEIGHT;
						$heightScale = self::BIOME_SCALE_OFFSET + $nearBiomeHeight->getScale() * self::BIOME_SCALE_WEIGHT;
						if($this->type === WorldType::AMPLIFIED && $heightBase > 0){
							$heightBase = 1.0 + $heightBase * 2.0;
							$heightScale = 1.0 + $heightScale * 4.0;
						}

						$weight = self::$ELEVATION_WEIGHT[self::elevationWeightHash($m, $n)] / ($heightBase + 2.0);
						if($nearBiomeHeight->getHeight() > $biomeHeight->getHeight()){
							$weight *= 0.5;
						}

						$avgHeightScale += $heightScale * $weight;
						$avgHeightBase += $heightBase * $weight;
						$totalWeight += $weight;
					}
				}
				$avgHeightScale /= $totalWeight;
				$avgHeightBase /= $totalWeight;
				$avgHeightScale = $avgHeightScale * 0.9 + 0.1;
				$avgHeightBase = ($avgHeightBase * 4.0 - 1.0) / 8.0;

				$noiseH = $heightNoise[$indexHeight++] / 8000.0;
				if($noiseH < 0){
					$noiseH = abs($noiseH) * 0.3;
				}

				$noiseH = $noiseH * 3.0 - 2.0;
				if($noiseH < 0){
					$noiseH = max($noiseH * 0.5, -1) / 1.4 * 0.5;
				}else{
					$noiseH = min($noiseH, 1) / 8.0;
				}

				$noiseH = ($noiseH * 0.2 + $avgHeightBase) * self::BASE_SIZE / 8.0 * 4.0 + self::BASE_SIZE;
				for($k = 0; $k < 33; ++$k){
					// density should be lower and lower as we climb up, this gets a height value to
					// subtract from the noise.
					$nh = ($k - $noiseH) * self::STRETCH_Y * 128.0 / 256.0 / $avgHeightScale;
					if($nh < 0.0){
						$nh *= 4.0;
					}

					$noiseR = $roughnessNoise[$index] / 512.0;
					$noiseR2 = $roughnessNoise2[$index] / 512.0;
					$noiseD = ($detailNoise[$index] / 10.0 + 1.0) / 2.0;

					// linear interpolation
					$dens = $noiseD < 0 ? $noiseR : ($noiseD > 1 ? $noiseR2 : $noiseR + ($noiseR2 - $noiseR) * $noiseD);
					$dens -= $nh;
					++$index;
					if($k > 29){
						$lowering = ($k - 29) / 3.0;
						// linear interpolation
						$dens = $dens * (1.0 - $lowering) + -10.0 * $lowering;
					}
					$density[self::densityHash($i, $j, $k)] = $dens;
				}
			}
		}
		return $density;
	}
}

OverworldGenerator::init();