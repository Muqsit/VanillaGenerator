<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\cave;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\utils\Random;
use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\world\format\Chunk;

/**
 * Natural cave generator that mimics Minecraft's organic cave systems
 * Creates winding, natural-looking tunnels and caverns
 */
class CaveGenerator {
	
	// More natural cave generation parameters
	private const CAVE_FREQUENCY = 0.08;    // 8% chance - more realistic frequency
	private const CAVERN_FREQUENCY = 0.03;  // 3% chance for large caverns
	private const TUNNEL_LENGTH_MIN = 40;   // Longer, more natural tunnels
	private const TUNNEL_LENGTH_MAX = 120;
	private const CAVERN_SIZE_MIN = 6;
	private const CAVERN_SIZE_MAX = 15;
	
	// Y-level constraints - 1.18+ world height support
	private const MIN_CAVE_Y = -55; // Caves can go much deeper now
	private const MAX_CAVE_Y = 50;  // Caves can still reach near surface
	private const SURFACE_BUFFER = 10;
	
	// Aquifer settings - 1.18+ world height support
	private const AQUIFER_BASE = 0;         // Water level around Y=0 (new sea level area)
	private const AQUIFER_VARIANCE = 6;     // Variance range (Y=-6 to Y=6)
	private const LAVA_LEVEL = -54;         // Lava appears in deep caves (below Y=-54)
	private const WATER_FILL_CHANCE = 0.12; // 12% of chunks get water aquifers
	
	private Random $random;
	private SimplexOctaveGenerator $aquiferNoise;

	public function __construct(int $seed) {
		$this->random = new Random($seed);
		
		// 2D noise for aquifer levels
		$this->aquiferNoise = SimplexOctaveGenerator::fromRandomAndOctaves(new Random($seed + 3), 2, 16, 1, 16);
		$this->aquiferNoise->setScale(1.0 / 32.0);
	}

	/**
	 * Generate natural cave systems that flow organically through chunks
	 */
	public function carveDirectly(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunk === null) return;
		
		// Use regional seeding for consistent cave generation across chunks
		$this->generateRegionalCaves($world, $chunkX, $chunkZ);
	}
	
	/**
	 * Generate caves considering a larger region for natural flow
	 */
	private function generateRegionalCaves(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$carvedBlocks = 0;
		
		// Check a 3x3 region of chunks centered on current chunk for cave generation
		for ($regionX = $chunkX - 1; $regionX <= $chunkX + 1; $regionX++) {
			for ($regionZ = $chunkZ - 1; $regionZ <= $chunkZ + 1; $regionZ++) {
				// Seed based on the region chunk for consistent generation
				$this->random->setSeed($regionX * 341873128712 + $regionZ * 132897987541);
				
				// Generate caves that might flow into our target chunk
				$caveAttempts = 6 + $this->random->nextBoundedInt(8); // 6-13 attempts per region chunk
				
				for ($i = 0; $i < $caveAttempts; $i++) {
					if ($this->random->nextFloat() < self::CAVE_FREQUENCY) {
						$regionBaseX = $regionX << 4;
						$regionBaseZ = $regionZ << 4;
						
						// Start somewhere in the region chunk
						$startX = $regionBaseX + $this->random->nextBoundedInt(16);
						$startY = self::MIN_CAVE_Y + $this->random->nextBoundedInt(self::MAX_CAVE_Y - self::MIN_CAVE_Y);
						$startZ = $regionBaseZ + $this->random->nextBoundedInt(16);
						
						$carvedBlocks += $this->generateNaturalCaveSystem($world, $startX, $startY, $startZ, $chunkX, $chunkZ);
					}
				}
				
				// Generate occasional large caverns
				if ($this->random->nextFloat() < self::CAVERN_FREQUENCY) {
					$regionBaseX = $regionX << 4;
					$regionBaseZ = $regionZ << 4;
					
					$centerX = $regionBaseX + $this->random->nextBoundedInt(16);
					$centerY = self::MIN_CAVE_Y + $this->random->nextBoundedInt(self::MAX_CAVE_Y - self::MIN_CAVE_Y);
					$centerZ = $regionBaseZ + $this->random->nextBoundedInt(16);
					
					$carvedBlocks += $this->generateNaturalCavern($world, $centerX, $centerY, $centerZ, $chunkX, $chunkZ);
				}
			}
		}
		
		// if ($carvedBlocks > 0) {
		// 	error_log("CaveGenerator: Carved $carvedBlocks blocks in chunk $chunkX, $chunkZ");
		// }
	}
	
	/**
	 * Generate a natural winding cave system
	 */
	private function generateNaturalCaveSystem(ChunkManager $world, int $startX, int $startY, int $startZ, int $targetChunkX, int $targetChunkZ): int {
		$length = self::TUNNEL_LENGTH_MIN + $this->random->nextBoundedInt(self::TUNNEL_LENGTH_MAX - self::TUNNEL_LENGTH_MIN);
		
		$x = (float)$startX;
		$y = (float)$startY;
		$z = (float)$startZ;
		
		// Start with random direction
		$yaw = $this->random->nextFloat() * M_PI * 2.0;
		$pitch = ($this->random->nextFloat() - 0.5) * 0.4;
		
		$carvedBlocks = 0;
		
		for ($i = 0; $i < $length; $i++) {
			// Natural radius variation - more organic shape
			$progress = $i / (float)$length;
			$baseRadius = 1.5 + $this->random->nextFloat() * 2.0; // 1.5-3.5 base radius
			
			// Add natural variation using sine wave and random factors
			$sizeVariation = sin($progress * M_PI * 3) * 0.5 + ($this->random->nextFloat() - 0.5) * 0.8;
			$radius = max(0.8, $baseRadius + $sizeVariation);
			
			// Carve current position
			$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);
			
			// Natural direction changes - more organic movement
			$yawChange = ($this->random->nextFloat() - 0.5) * 0.25; // More natural turning
			$pitchChange = ($this->random->nextFloat() - 0.5) * 0.15;
			
			// Occasionally make sharper turns for more interesting caves
			if ($this->random->nextFloat() < 0.05) { // 5% chance for sharp turn
				$yawChange = ($this->random->nextFloat() - 0.5) * 0.8;
				$pitchChange = ($this->random->nextFloat() - 0.5) * 0.4;
			}
			
			$yaw += $yawChange;
			$pitch += $pitchChange;
			
			// Keep pitch reasonable but allow more variation than before
			$pitch = max(-0.6, min(0.6, $pitch));
			
			// Natural movement - variable speed
			$moveSpeed = 0.7 + $this->random->nextFloat() * 0.6; // 0.7-1.3 speed variation
			
			$x += cos($yaw) * cos($pitch) * $moveSpeed;
			$y += sin($pitch) * $moveSpeed;
			$z += sin($yaw) * cos($pitch) * $moveSpeed;
			
			// Natural branching - less frequent but more organic
			if ($this->random->nextFloat() < 0.04 && $i > 15) { // 4% chance after 15 blocks
				$branchLength = 20 + $this->random->nextBoundedInt(40);
				$carvedBlocks += $this->generateNaturalBranch($world, $x, $y, $z, $branchLength, $targetChunkX, $targetChunkZ);
			}
			
			// Very rarely, create a small chamber
			if ($this->random->nextFloat() < 0.008 && $i > 20) { // 0.8% chance
				$carvedBlocks += $this->generateSmallChamber($world, $x, $y, $z, $targetChunkX, $targetChunkZ);
			}
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Generate natural branch tunnels
	 */
	private function generateNaturalBranch(ChunkManager $world, float $startX, float $startY, float $startZ, int $length, int $targetChunkX, int $targetChunkZ): int {
		$x = $startX;
		$y = $startY;
		$z = $startZ;
		
		// Branch direction - split off from main tunnel naturally
		$yaw = $this->random->nextFloat() * M_PI * 2.0;
		$pitch = ($this->random->nextFloat() - 0.5) * 0.5;
		
		$carvedBlocks = 0;
		
		for ($i = 0; $i < $length; $i++) {
			// Branches get smaller as they extend
			$progress = $i / (float)$length;
			$radius = 2.0 - $progress * 0.8 + ($this->random->nextFloat() - 0.5) * 0.4; // 2.0 down to ~1.2
			$radius = max(0.6, $radius);
			
			$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);
			
			// Natural branch movement
			$yaw += ($this->random->nextFloat() - 0.5) * 0.2;
			$pitch += ($this->random->nextFloat() - 0.5) * 0.12;
			$pitch = max(-0.4, min(0.4, $pitch));
			
			// Variable movement speed for branches
			$moveSpeed = 0.6 + $this->random->nextFloat() * 0.4;
			
			$x += cos($yaw) * cos($pitch) * $moveSpeed;
			$y += sin($pitch) * $moveSpeed;
			$z += sin($yaw) * cos($pitch) * $moveSpeed;
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Generate small natural chambers
	 */
	private function generateSmallChamber(ChunkManager $world, float $centerX, float $centerY, float $centerZ, int $targetChunkX, int $targetChunkZ): int {
		$size = 4 + $this->random->nextBoundedInt(6); // 4-9 block chambers
		$carvedBlocks = 0;
		
		// Create irregular chamber with 2-4 overlapping spheres
		$sphereCount = 2 + $this->random->nextBoundedInt(3);
		
		for ($i = 0; $i < $sphereCount; $i++) {
			$offsetX = $centerX + ($this->random->nextFloat() - 0.5) * $size * 0.5;
			$offsetY = $centerY + ($this->random->nextFloat() - 0.5) * $size * 0.3;
			$offsetZ = $centerZ + ($this->random->nextFloat() - 0.5) * $size * 0.5;
			
			$radius = 2.5 + $this->random->nextFloat() * 2.0; // 2.5-4.5 radius
			
			$carvedBlocks += $this->carveSphere($world, (int)round($offsetX), (int)round($offsetY), (int)round($offsetZ), $radius, $targetChunkX, $targetChunkZ);
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Generate natural large caverns
	 */
	private function generateNaturalCavern(ChunkManager $world, int $centerX, int $centerY, int $centerZ, int $targetChunkX, int $targetChunkZ): int {
		$sizeX = self::CAVERN_SIZE_MIN + $this->random->nextBoundedInt(self::CAVERN_SIZE_MAX - self::CAVERN_SIZE_MIN);
		$sizeY = (int)($sizeX * 0.6) + $this->random->nextBoundedInt((int)($sizeX * 0.3));
		$sizeZ = self::CAVERN_SIZE_MIN + $this->random->nextBoundedInt(self::CAVERN_SIZE_MAX - self::CAVERN_SIZE_MIN);
		
		$carvedBlocks = 0;
		
		// Create natural, irregular cavern shape
		$sphereCount = 4 + $this->random->nextBoundedInt(7); // 4-10 spheres
		
		for ($i = 0; $i < $sphereCount; $i++) {
			// More natural sphere placement
			$angle = ($i / (float)$sphereCount) * M_PI * 2.0 + ($this->random->nextFloat() - 0.5) * 1.0;
			$distance = $this->random->nextFloat() * $sizeX * 0.4;
			
			$offsetX = $centerX + cos($angle) * $distance;
			$offsetY = $centerY + ($this->random->nextFloat() - 0.5) * $sizeY * 0.8;
			$offsetZ = $centerZ + sin($angle) * $distance;
			
			$radius = 3.0 + $this->random->nextFloat() * 4.0; // 3.0-7.0 radius
			
			$carvedBlocks += $this->carveSphere($world, (int)round($offsetX), (int)round($offsetY), (int)round($offsetZ), $radius, $targetChunkX, $targetChunkZ);
		}
		
		// Add some natural tunnels leading from the cavern
		$tunnelCount = 2 + $this->random->nextBoundedInt(4); // 2-5 tunnels
		for ($i = 0; $i < $tunnelCount; $i++) {
			$tunnelLength = 15 + $this->random->nextBoundedInt(30);
			$yaw = $this->random->nextFloat() * M_PI * 2.0;
			$pitch = ($this->random->nextFloat() - 0.5) * 0.4;
			
			$x = (float)$centerX;
			$y = (float)$centerY;
			$z = (float)$centerZ;
			
			for ($j = 0; $j < $tunnelLength; $j++) {
				$progress = $j / (float)$tunnelLength;
				$radius = 2.8 - $progress * 1.0; // Taper from 2.8 to 1.8
				$radius = max(1.0, $radius);
				
				$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);
				
				// Natural tunnel movement
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
	 * Carve a spherical area (for tunnels and caverns)
	 */
	private function carveSphere(ChunkManager $world, int $centerX, int $centerY, int $centerZ, float $radius, int $targetChunkX, int $targetChunkZ): int {
		$chunk = $world->getChunk($targetChunkX, $targetChunkZ);
		if ($chunk === null) return 0;
		
		$chunkBaseX = $targetChunkX << 4;
		$chunkBaseZ = $targetChunkZ << 4;
		
		$carvedBlocks = 0;
		$radiusSquared = $radius * $radius;
		
		// Only carve within the target chunk boundaries
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
					
					// Calculate distance from center
					$dx = $worldX - $centerX;
					$dy = $worldY - $centerY;
					$dz = $worldZ - $centerZ;
					$distanceSquared = $dx * $dx + $dy * $dy + $dz * $dz;
					
					// Only carve if within radius and the block is stone
					if ($distanceSquared <= $radiusSquared) {
						$block = $chunk->getBlockStateId($x, $y, $z);
						if ($block === VanillaBlocks::STONE()->getStateId()) {
							$chunk->setBlockStateId($x, $y, $z, VanillaBlocks::AIR()->getStateId());
							$carvedBlocks++;
						}
					}
				}
			}
		}
		
		return $carvedBlocks;
	}
	
	/**
	 * Apply natural aquifer system with proper water and lava placement
	 */
	public function applyAquifers(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunk === null) return;
		
		$baseX = $chunkX << 4;
		$baseZ = $chunkZ << 4;
		
		// Reset random for consistent aquifer placement
		$this->random->setSeed($chunkX * 871236847 + $chunkZ * 321487613);
		
		// Water pools disabled by request; only generate lava pools
		$this->applyLavaPools($chunk, $chunkX, $chunkZ);
	}
	
	/**
	 * Apply water aquifers - only in specific areas, not filling all caves
	 */
	private function applyWaterAquifers(Chunk $chunk, int $chunkX, int $chunkZ): void {
		// Disabled: do not place water in caves
		return;
		$baseX = $chunkX << 4;
		$baseZ = $chunkZ << 4;
		
		// Only some chunks get water aquifers
		if ($this->random->nextFloat() > self::WATER_FILL_CHANCE) {
			return;
		}
		
		// Generate aquifer level map for chunk
		$aquiferMap = $this->aquiferNoise->getFractalBrownianMotion($baseX, 0, $baseZ, 0.5, 0.5);
		
		$waterCount = 0;
		
		// Find connected cave areas and fill them with water up to a certain level
		for ($x = 0; $x < 16; ++$x) {
			for ($z = 0; $z < 16; ++$z) {
				$index = $x | ($z << 4);
				$localWaterLevel = (int)(self::AQUIFER_BASE + $aquiferMap[$index] * self::AQUIFER_VARIANCE);
				
				// Only create water if there's a cave opening at this location
				$hasWaterSource = false;
				for ($y = $localWaterLevel; $y >= self::MIN_CAVE_Y; $y--) {
					$block = $chunk->getBlockStateId($x, $y, $z);
					
					if ($block === VanillaBlocks::AIR()->getStateId()) {
						$hasWaterSource = true;
						break;
					}
				}
				
				// Fill connected cave areas with water up to the water level
				if ($hasWaterSource) {
					for ($y = self::MIN_CAVE_Y; $y <= $localWaterLevel && $y < self::MAX_CAVE_Y; ++$y) {
						$block = $chunk->getBlockStateId($x, $y, $z);
						
						if ($block === VanillaBlocks::AIR()->getStateId()) {
							$chunk->setBlockStateId($x, $y, $z, VanillaBlocks::WATER()->getStateId());
							$waterCount++;
						}
					}
				}
			}
		}
		
		// if ($waterCount > 0) {
		//     error_log("CaveGenerator: Applied $waterCount water blocks in chunk $chunkX, $chunkZ");
		// }
	}
	
	/**
	 * Apply lava pools - small isolated pools, not connected systems
	 */
	private function applyLavaPools(Chunk $chunk, int $chunkX, int $chunkZ): void {
		// Chance for lava pools to generate
		$lavaPoolChance = 0.08; // 8% chance per chunk
		
		if ($this->random->nextFloat() > $lavaPoolChance) {
			return;
		}
		
		$lavaCount = 0;
		$poolsGenerated = 0;
		$maxPools = 1 + $this->random->nextBoundedInt(3); // 1-3 lava pools per chunk
		
		for ($poolAttempt = 0; $poolAttempt < $maxPools; $poolAttempt++) {
			// Random location for lava pool
			$poolX = $this->random->nextBoundedInt(16);
			$poolZ = $this->random->nextBoundedInt(16);
			$poolY = self::MIN_CAVE_Y + $this->random->nextBoundedInt(self::LAVA_LEVEL - self::MIN_CAVE_Y + 1);
			
			// Check if there's a cave area here
			$block = $chunk->getBlockStateId($poolX, $poolY, $poolZ);
			if ($block !== VanillaBlocks::AIR()->getStateId()) {
				continue; // No cave here, try next location
			}
			
			// Create small lava pool (3-7 blocks wide)
			$poolSize = 3 + $this->random->nextBoundedInt(5);
			$poolRadius = $poolSize / 2.0;
			
			$poolLavaCount = 0;
			
			for ($dx = -$poolSize; $dx <= $poolSize; $dx++) {
				for ($dz = -$poolSize; $dz <= $poolSize; $dz++) {
					for ($dy = -2; $dy <= 1; $dy++) { // Lava pools are shallow
						$lx = $poolX + $dx;
						$lz = $poolZ + $dz;
						$ly = $poolY + $dy;
						
						// Check bounds
						if ($lx < 0 || $lx >= 16 || $lz < 0 || $lz >= 16 || 
							$ly < self::MIN_CAVE_Y || $ly > self::LAVA_LEVEL) {
							continue;
						}
						
						// Check if within pool radius
						$distance = sqrt($dx * $dx + $dz * $dz + $dy * $dy * 0.5); // Flatten vertically
						if ($distance > $poolRadius) {
							continue;
						}
						
						$currentBlock = $chunk->getBlockStateId($lx, $ly, $lz);
						
						// Only place lava in air blocks (cave areas)
						if ($currentBlock === VanillaBlocks::AIR()->getStateId()) {
							$chunk->setBlockStateId($lx, $ly, $lz, VanillaBlocks::LAVA()->getStateId());
							$poolLavaCount++;
							$lavaCount++;
						}
					}
				}
			}
			
			// Only count as successful pool if we placed enough lava
			if ($poolLavaCount >= 5) {
				$poolsGenerated++;
			}
		}
		
		// if ($lavaCount > 0) {
		//     error_log("CaveGenerator: Generated $poolsGenerated lava pools ($lavaCount lava blocks) in chunk $chunkX, $chunkZ");
		// }
	}
}