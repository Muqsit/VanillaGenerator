<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\cave;

use pocketmine\block\VanillaBlocks;
use pocketmine\world\ChunkManager;
use pocketmine\utils\Random;
use muqsit\vanillagenerator\generator\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\world\format\Chunk;

class CaveGenerator {

	private const CAVE_FREQUENCY = 0.08;
	private const CAVERN_FREQUENCY = 0.03;
	private const TUNNEL_LENGTH_MIN = 40;
	private const TUNNEL_LENGTH_MAX = 120;
	private const CAVERN_SIZE_MIN = 6;
	private const CAVERN_SIZE_MAX = 15;
	
	private const MIN_CAVE_Y = -59;
	private const MAX_CAVE_Y = 50;

	private const LAVA_LEVEL = -54;
	
	private Random $random;
	private SimplexOctaveGenerator $aquiferNoise;

	public function __construct(int $seed) {
		$this->random = new Random($seed);
		
		$this->aquiferNoise = SimplexOctaveGenerator::fromRandomAndOctaves(new Random($seed + 3), 2, 16, 1, 16);
		$this->aquiferNoise->setScale(1.0 / 32.0);
	}

	/**
	 * Generate natural cave systems that flow organically through chunks
	 */
	public function carveDirectly(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunk === null) return;
		
		$this->generateRegionalCaves($world, $chunkX, $chunkZ);
	}
	
	/**
	 * Generate caves considering a larger region for natural flow
	 */
	private function generateRegionalCaves(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$carvedBlocks = 0;
		
		for ($regionX = $chunkX - 1; $regionX <= $chunkX + 1; $regionX++) {
			for ($regionZ = $chunkZ - 1; $regionZ <= $chunkZ + 1; $regionZ++) {
				$this->random->setSeed($regionX * 341873128712 + $regionZ * 132897987541);
				
				$caveAttempts = 6 + $this->random->nextBoundedInt(8);
				
				for ($i = 0; $i < $caveAttempts; $i++) {
					if ($this->random->nextFloat() < self::CAVE_FREQUENCY) {
						$regionBaseX = $regionX << 4;
						$regionBaseZ = $regionZ << 4;
						
						$startX = $regionBaseX + $this->random->nextBoundedInt(16);
						$startY = self::MIN_CAVE_Y + $this->random->nextBoundedInt(self::MAX_CAVE_Y - self::MIN_CAVE_Y);
						$startZ = $regionBaseZ + $this->random->nextBoundedInt(16);
						
						$carvedBlocks += $this->generateNaturalCaveSystem($world, $startX, $startY, $startZ, $chunkX, $chunkZ);
					}
				}
				
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
	}
	
	/**
	 * Generate a natural winding cave system
	 */
	private function generateNaturalCaveSystem(ChunkManager $world, int $startX, int $startY, int $startZ, int $targetChunkX, int $targetChunkZ): int {
		$length = self::TUNNEL_LENGTH_MIN + $this->random->nextBoundedInt(self::TUNNEL_LENGTH_MAX - self::TUNNEL_LENGTH_MIN);
		
		$x = (float)$startX;
		$y = (float)$startY;
		$z = (float)$startZ;
		
		$yaw = $this->random->nextFloat() * M_PI * 2.0;
		$pitch = ($this->random->nextFloat() - 0.5) * 0.4;
		
		$carvedBlocks = 0;
		
		for ($i = 0; $i < $length; $i++) {
			$progress = $i / (float)$length;
			$baseRadius = 1.5 + $this->random->nextFloat() * 2.0;
			
			$sizeVariation = sin($progress * M_PI * 3) * 0.5 + ($this->random->nextFloat() - 0.5) * 0.8;
			$radius = max(0.8, $baseRadius + $sizeVariation);
			
			$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);
			
			$yawChange = ($this->random->nextFloat() - 0.5) * 0.25;
			$pitchChange = ($this->random->nextFloat() - 0.5) * 0.15;
			
			if ($this->random->nextFloat() < 0.05) {
				$yawChange = ($this->random->nextFloat() - 0.5) * 0.8;
				$pitchChange = ($this->random->nextFloat() - 0.5) * 0.4;
			}
			
			$yaw += $yawChange;
			$pitch += $pitchChange;
			
			$pitch = max(-0.6, min(0.6, $pitch));
			
			$moveSpeed = 0.7 + $this->random->nextFloat() * 0.6;
			
			$x += cos($yaw) * cos($pitch) * $moveSpeed;
			$y += sin($pitch) * $moveSpeed;
			$z += sin($yaw) * cos($pitch) * $moveSpeed;
			
			if ($this->random->nextFloat() < 0.04 && $i > 15) {
				$branchLength = 20 + $this->random->nextBoundedInt(40);
				$carvedBlocks += $this->generateNaturalBranch($world, $x, $y, $z, $branchLength, $targetChunkX, $targetChunkZ);
			}
			
			if ($this->random->nextFloat() < 0.008 && $i > 20) {
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
		
		$yaw = $this->random->nextFloat() * M_PI * 2.0;
		$pitch = ($this->random->nextFloat() - 0.5) * 0.5;
		
		$carvedBlocks = 0;
		
		for ($i = 0; $i < $length; $i++) {
			$progress = $i / (float)$length;
			$radius = 2.0 - $progress * 0.8 + ($this->random->nextFloat() - 0.5) * 0.4; // 2.0 down to ~1.2
			$radius = max(0.6, $radius);
			
			$carvedBlocks += $this->carveSphere($world, (int)round($x), (int)round($y), (int)round($z), $radius, $targetChunkX, $targetChunkZ);
			
			$yaw += ($this->random->nextFloat() - 0.5) * 0.2;
			$pitch += ($this->random->nextFloat() - 0.5) * 0.12;
			$pitch = max(-0.4, min(0.4, $pitch));
			
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
			$tunnelLength = 15 + $this->random->nextBoundedInt(30);
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
	 * Carve a spherical area (for tunnels and caverns)
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
	 * Apply natural aquifer system with proper water and lava placement
	 */
	public function applyAquifers(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunk === null) return;
		
		$baseX = $chunkX << 4;
		$baseZ = $chunkZ << 4;

		$this->random->setSeed($chunkX * 871236847 + $chunkZ * 321487613);
		
		$this->applyLavaPools($chunk, $chunkX, $chunkZ);
	}
	
	/**
	 * Apply lava pools - small isolated pools, not connected systems
	 */
	private function applyLavaPools(Chunk $chunk, int $chunkX, int $chunkZ): void {
		$airId = VanillaBlocks::AIR()->getStateId();
		$lavaId = VanillaBlocks::LAVA()->getStateId();
		$y = self::LAVA_LEVEL; // -54
		for ($x = 0; $x < 16; $x++) {
			for ($z = 0; $z < 16; $z++) {
				if ($chunk->getBlockStateId($x, $y, $z) === $airId) {
					$chunk->setBlockStateId($x, $y, $z, $lavaId);
				}
			}
		}
	}
}