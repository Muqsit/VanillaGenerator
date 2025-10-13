<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\cave;

use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\world\format\Chunk;

class CaveGenerator {

	private const CAVE_FREQUENCY = 0.08;
	private const CAVERN_FREQUENCY = 0.03;

	private const TUNNEL_LENGTH_MIN = 380;
	private const TUNNEL_LENGTH_MAX = 900;
	private const CAVERN_SIZE_MIN = 8;
	private const CAVERN_SIZE_MAX = 15;
	
	private const MIN_CAVE_Y = -59;
	private const MAX_CAVE_Y = 50;

	private const LAVA_LEVEL = -54;
	private const LAVA_MIN_Y = -62;
	
	private Random $random;
	private int $seed;
	private SimplexOctaveGenerator $flowNoise;

	public function __construct(int $seed) {
		$this->seed = $seed;
		$this->random = new Random($seed);

        // Low-frequency field to steer tunnels (Perlin worms)
        $this->flowNoise = SimplexOctaveGenerator::fromRandomAndOctaves(new Random($seed + 11), 3, 1, 0, 0);
        $this->flowNoise->setScale(1.0 / 96.0);
	}

	/**
	 * Carve caves in the specified chunk
	 * @param ChunkManager $world the affected world
	 * @param int $chunkX chunk X coordinate
	 * @param int $chunkZ chunk Z coordinate
	 */
	public function carveDirectly(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunk === null) return;
		
		$this->generateRegionalCaves($world, $chunkX, $chunkZ);
	}
	
	/**
	 * Generate caves considering a larger region for natural flow
	 * @param ChunkManager $world the affected world
	 * @param int $chunkX chunk X coordinate
	 * @param int $chunkZ chunk Z coordinate
	 */
	private function generateRegionalCaves(ChunkManager $world, int $chunkX, int $chunkZ): void {
		for ($regionX = $chunkX - 1; $regionX <= $chunkX + 1; $regionX++) {
			for ($regionZ = $chunkZ - 1; $regionZ <= $chunkZ + 1; $regionZ++) {
				// Include world seed so cave layout differs between worlds
				$this->random->setSeed(($regionX * 341873128712 + $regionZ * 132897987541) ^ $this->seed);
				
				$caveAttempts = 6 + $this->random->nextBoundedInt(8);
				
				for ($i = 0; $i < $caveAttempts; $i++) {
					if ($this->random->nextFloat() < self::CAVE_FREQUENCY) {
						$regionBaseX = $regionX << 4;
						$regionBaseZ = $regionZ << 4;
						
						$startX = $regionBaseX + $this->random->nextBoundedInt(16);
						$startY = self::MIN_CAVE_Y + $this->random->nextBoundedInt(self::MAX_CAVE_Y - self::MIN_CAVE_Y);
						$startZ = $regionBaseZ + $this->random->nextBoundedInt(16);
						$this->generateCaveSystem($world, $startX, $startY, $startZ, $chunkX, $chunkZ);
					}
				}

				if ($this->random->nextFloat() < self::CAVERN_FREQUENCY) {
					$regionBaseX = $regionX << 4;
					$regionBaseZ = $regionZ << 4;
					
					$centerX = $regionBaseX + $this->random->nextBoundedInt(16);
					$centerY = self::MIN_CAVE_Y + $this->random->nextBoundedInt(self::MAX_CAVE_Y - self::MIN_CAVE_Y);
					$centerZ = $regionBaseZ + $this->random->nextBoundedInt(16);
					$this->generateNaturalCavern($world, $centerX, $centerY, $centerZ, $chunkX, $chunkZ);
				}

			}
		}
	}
	
	/**
	 * Generate SpaghettiCaves: Perlin-worm style long, curvy tunnels with occasional branches and chambers.
	 * @param ChunkManager $world the affected world
	 * @param int $startX starting X coordinate
	 * @param int $startY starting Y coordinate
	 * @param int $startZ starting Z coordinate
	 * @param int $targetChunkX chunk X coordinate to limit carving to
	 * @param int $targetChunkZ chunk Z coordinate to limit carving to
	 * @return int number of blocks carved
	 */
	private function generateCaveSystem(ChunkManager $world, int $startX, int $startY, int $startZ, int $targetChunkX, int $targetChunkZ): int {
		$length = self::TUNNEL_LENGTH_MIN + $this->random->nextBoundedInt(self::TUNNEL_LENGTH_MAX - self::TUNNEL_LENGTH_MIN);

		$x = (float)$startX;
		$y = (float)$startY;
		$z = (float)$startZ;

		// Initialize direction as a 3D unit vector
		$yaw = $this->random->nextFloat() * M_PI * 2.0;
		$pitch = ($this->random->nextFloat() - 0.5) * 0.25;

		// Perlin-worm
		$yawStrength = 0.35;
		$pitchStrength = 0.22;
		$maxPitch = 0.46;
		$speed = 0.95;

		$carvedBlocks = 0;

		for ($i = 0; $i < $length; $i++) {
			$progress = $i / (float)$length;
			$baseRadius = 1.9 + $this->random->nextFloat() * 1.9; // thicker tunnels

			// Mild size variation to keep shape organic but not jagged
			$sizeVariation = sin($progress * M_PI * 2.4) * 0.35 + ($this->random->nextFloat() - 0.5) * 0.4;
			$radius = max(1.1, $baseRadius + $sizeVariation);

			$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);

			// Perlin-worm heading updates from flow field
			$noiseYaw = $this->flowNoise->noise($x, $y, $z, 2.0, 0.5, true);                     // [-1,1]
			$noisePitch = $this->flowNoise->noise($x + 1000.0, $y + 1000.0, $z + 1000.0, 2.0, 0.5, true); // decorrelated
			$yaw += $noiseYaw * $yawStrength;
			$pitch += $noisePitch * $pitchStrength;
			$pitch = max(-$maxPitch, min($maxPitch, $pitch));

			$x += cos($yaw) * cos($pitch) * $speed;
			$y += sin($pitch) * $speed;
			$z += sin($yaw) * cos($pitch) * $speed;

			// Keep within reasonable Y bounds by reflecting direction softly
			if ($y < self::MIN_CAVE_Y + 2) {
				$y = (float)(self::MIN_CAVE_Y + 2);
				$pitch = abs($pitch); // go up
			} elseif ($y > self::MAX_CAVE_Y - 2) {
				$y = (float)(self::MAX_CAVE_Y - 2);
				$pitch = -abs($pitch); // go down
			}

			// Less frequent branches, but longer
			if ($this->random->nextFloat() < 0.03 && $i > 30) {
				$branchLength = 100 + $this->random->nextBoundedInt(140);
				$carvedBlocks += $this->generateBranch($world, $x, $y, $z, $branchLength, $targetChunkX, $targetChunkZ);
			}

			// Chambers occur rarely to keep the "worm" feel
			if ($this->random->nextFloat() < 0.006 && $i > 40) {
				$carvedBlocks += $this->generateSmallChamber($world, $x, $y, $z, $targetChunkX, $targetChunkZ);
			}
		}

		return $carvedBlocks;
	}
	
	/**
	 * Generate natural branch tunnels
	 * @param ChunkManager $world the affected world
	 * @param float $startX starting X coordinate
	 * @param float $startY starting Y coordinate
	 * @param float $startZ starting Z coordinate
	 * @param int $length length of the branch
	 * @param int $targetChunkX chunk X coordinate to limit carving to
	 * @param int $targetChunkZ chunk Z coordinate to limit carving to
	 * @return int number of blocks carved
	 */
	private function generateBranch(ChunkManager $world, float $startX, float $startY, float $startZ, int $length, int $targetChunkX, int $targetChunkZ): int {
		$x = $startX;
		$y = $startY;
		$z = $startZ;
		
		$yaw = $this->random->nextFloat() * M_PI * 2.0;
		$pitch = ($this->random->nextFloat() - 0.5) * 0.3;
		
		$carvedBlocks = 0;
		
		for ($i = 0; $i < $length; $i++) {
			$progress = $i / (float)$length;
			$radius = 2.1 - $progress * 0.55 + ($this->random->nextFloat() - 0.5) * 0.38;
			$radius = max(0.9, $radius);
			
			$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);
			
			// steer by flow field (weaker than main tunnels)
			$noiseYaw = $this->flowNoise->noise($x, $y, $z, 2.0, 0.5, true);
			$noisePitch = $this->flowNoise->noise($x + 1000.0, $y + 1000.0, $z + 1000.0, 2.0, 0.5, true);
			$yaw += $noiseYaw * 0.22;
			$pitch += $noisePitch * 0.15;
			$pitch = max(-0.35, min(0.35, $pitch));
			
			$moveSpeed = 0.7 + $this->random->nextFloat() * 0.35;
			
			$x += cos($yaw) * cos($pitch) * $moveSpeed;
			$y += sin($pitch) * $moveSpeed;
			$z += sin($yaw) * cos($pitch) * $moveSpeed;
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Generate a small chamber by overlapping several spheres
	 * @param ChunkManager $world the affected world
	 * @param float $centerX center X coordinate
	 * @param float $centerY center Y coordinate
	 * @param float $centerZ center Z coordinate
	 * @param int $targetChunkX chunk X coordinate to limit carving to
	 * @param int $targetChunkZ chunk Z coordinate to limit carving to
	 * @return int number of blocks carved
	 */
	private function generateSmallChamber(ChunkManager $world, float $centerX, float $centerY, float $centerZ, int $targetChunkX, int $targetChunkZ): int {
		$size = 4 + $this->random->nextBoundedInt(6);
		$carvedBlocks = 0;
		
		$sphereCount = 2 + $this->random->nextBoundedInt(3);
		
		for ($i = 0; $i < $sphereCount; $i++) {
			$offsetX = $centerX + ($this->random->nextFloat() - 0.5) * $size * 0.5;
			$offsetY = $centerY + ($this->random->nextFloat() - 0.5) * $size * 0.3;
			$offsetZ = $centerZ + ($this->random->nextFloat() - 0.5) * $size * 0.5;
			
			$radius = 2.5 + $this->random->nextFloat() * 2.0;
			
			$carvedBlocks += $this->carveSphere($world, (int)round($offsetX), (int)round($offsetY), (int)round($offsetZ), $radius, $targetChunkX, $targetChunkZ);
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Generate natural large caverns
	 * @param ChunkManager $world the affected world
	 * @param int $centerX center X coordinate
	 * @param int $centerY center Y coordinate
	 * @param int $centerZ center Z coordinate
	 * @param int $targetChunkX chunk X coordinate to limit carving to
	 * @param int $targetChunkZ chunk Z coordinate to limit carving to
	 * @return int number of blocks carved
	 */
	private function generateNaturalCavern(ChunkManager $world, int $centerX, int $centerY, int $centerZ, int $targetChunkX, int $targetChunkZ): int {
		$sizeX = self::CAVERN_SIZE_MIN + $this->random->nextBoundedInt(self::CAVERN_SIZE_MAX - self::CAVERN_SIZE_MIN);
		$sizeY = (int)($sizeX * 0.6) + $this->random->nextBoundedInt((int)($sizeX * 0.3));
		$sizeZ = self::CAVERN_SIZE_MIN + $this->random->nextBoundedInt(self::CAVERN_SIZE_MAX - self::CAVERN_SIZE_MIN);
		
		$carvedBlocks = 0;
		
		$sphereCount = 4 + $this->random->nextBoundedInt(7); // 4-10 spheres
		
		for ($i = 0; $i < $sphereCount; $i++) {
			$angle = ($i / (float)$sphereCount) * M_PI * 2.0 + ($this->random->nextFloat() - 0.5) * 1.0;
			$distance = $this->random->nextFloat() * $sizeX * 0.4;
			
			$offsetX = $centerX + cos($angle) * $distance;
			$offsetY = $centerY + ($this->random->nextFloat() - 0.5) * $sizeY * 0.8;
			$offsetZ = $centerZ + sin($angle) * $distance;
			
			$radius = 3.0 + $this->random->nextFloat() * 4.0;
			
			$carvedBlocks += $this->carveSphere($world, (int)round($offsetX), (int)round($offsetY), (int)round($offsetZ), $radius, $targetChunkX, $targetChunkZ);
		}
		
		$tunnelCount = 2 + $this->random->nextBoundedInt(4);
		for ($i = 0; $i < $tunnelCount; $i++) {
			// Longer tunnels extending from caverns
			$tunnelLength = 60 + $this->random->nextBoundedInt(120);
			$yaw = $this->random->nextFloat() * M_PI * 2.0;
			$pitch = ($this->random->nextFloat() - 0.5) * 0.4;
			
			$x = (float)$centerX;
			$y = (float)$centerY;
			$z = (float)$centerZ;
			
			for ($j = 0; $j < $tunnelLength; $j++) {
				$progress = $j / (float)$tunnelLength;
				$radius = 2.8 - $progress * 1.0;
				$radius = max(1.0, $radius);
				
				$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);
				
				$yaw += ($this->random->nextFloat() - 0.5) * 0.15;
				$pitch += ($this->random->nextFloat() - 0.5) * 0.08;
				
				$x += cos($yaw) * cos($pitch) * 0.9;
				$y += sin($pitch) * 0.9;
				$z += sin($yaw) * cos($pitch) * 0.9;
			}
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Carve out a sphere of given radius at the specified coordinates within the target chunk
	 * @param ChunkManager $world the affected world
	 * @param int $centerX center X coordinate
	 * @param int $centerY center Y coordinate
	 * @param int $centerZ center Z coordinate
	 * @param float $radius radius of the sphere
	 * @param int $targetChunkX chunk X coordinate to limit carving to
	 * @param int $targetChunkZ chunk Z coordinate to limit carving to
	 * @return int number of blocks carved
	 */
	private function carveSphere(ChunkManager $world, int $centerX, int $centerY, int $centerZ, float $radius, int $targetChunkX, int $targetChunkZ): int {
		$chunk = $world->getChunk($targetChunkX, $targetChunkZ);
		if ($chunk === null) return 0;
		
		$chunkBaseX = $targetChunkX << 4;
		$chunkBaseZ = $targetChunkZ << 4;
		
		$carvedBlocks = 0;
		$radiusSquared = $radius * $radius;

		$airId = VanillaBlocks::AIR()->getStateId();
		$stoneId = VanillaBlocks::STONE()->getStateId();
		$deepslateId = VanillaBlocks::DEEPSLATE()->getStateId();
		
		$minX = max(0, $centerX - (int)ceil($radius) - $chunkBaseX);
		$maxX = min(15, $centerX + (int)ceil($radius) - $chunkBaseX);
		$minY = max(self::MIN_CAVE_Y, $centerY - (int)ceil($radius));
		$maxY = min(self::MAX_CAVE_Y, $centerY + (int)ceil($radius));
		$minZ = max(0, $centerZ - (int)ceil($radius) - $chunkBaseZ);
		$maxZ = min(15, $centerZ + (int)ceil($radius) - $chunkBaseZ);
		
		for ($x = $minX; $x <= $maxX; $x++) {
			for ($y = $minY; $y <= $maxY; $y++) {
				for ($z = $minZ; $z <= $maxZ; $z++) {
					$worldX = $chunkBaseX + $x;
					$worldY = $y;
					$worldZ = $chunkBaseZ + $z;
					
					$dx = $worldX - $centerX;
					$dy = $worldY - $centerY;
					$dz = $worldZ - $centerZ;
					$distanceSquared = $dx * $dx + $dy * $dy + $dz * $dz;
					
					if ($distanceSquared <= $radiusSquared) {
						$block = $chunk->getBlockStateId($x, $y, $z);
						if ($block === $stoneId || $block === $deepslateId) {
							$chunk->setBlockStateId($x, $y, $z, $airId);
							$carvedBlocks++;
						}
					}
				}
			}
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Apply aquifers such as lava pools at low depths
	 * @param ChunkManager $world the affected world
	 * @param int $chunkX chunk X coordinate
	 * @param int $chunkZ chunk Z coordinate
	 */
	public function applyAquifers(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunk === null) return;

		// Include world seed in aquifer-related randomness as well
		$this->random->setSeed(($chunkX * 871236847 + $chunkZ * 321487613) ^ $this->seed);
		
		$this->applyLavaPools($chunk);
	}
	
	/**
	 * Fill air pockets below a certain Y level with lava
	 * @param Chunk $chunk the affected chunk
	 */
	private function applyLavaPools(Chunk $chunk): void {
		$airId = VanillaBlocks::AIR()->getStateId();
		$lavaId = VanillaBlocks::LAVA()->getStateId();

		for ($x = 0; $x < 16; $x++) {
			for ($z = 0; $z < 16; $z++) {
				for ($y = self::LAVA_LEVEL; $y >= self::LAVA_MIN_Y; $y--) {
					if ($chunk->getBlockStateId($x, $y, $z) === $airId) {
						$chunk->setBlockStateId($x, $y, $z, $lavaId);
					}
				}
			}
		}
	}
}