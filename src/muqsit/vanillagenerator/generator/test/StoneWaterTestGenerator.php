<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\test;

use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use muqsit\vanillagenerator\generator\VanillaBiomeGrid;
use pocketmine\world\ChunkManager;

/**
 * Minimal test generator: same base terrain as Overworld (stone + water from raw density)
 * but without ground layer generation and without any populators/decorators/caves.
 *
 * Usage: set this class as the world generator to visualize only raw terrain.
 */
final class StoneWaterTestGenerator extends OverworldGenerator{

    public function __construct(int $seed, string $preset_string){
        // Parent sets up octaves; we'll override generation entrypoints to keep it minimal.
        parent::__construct($seed, $preset_string);
    }

    protected function generateChunkData(ChunkManager $world, int $chunk_x, int $chunk_z, VanillaBiomeGrid $grid) : void{
        // Only generate the raw stone/water terrain; skip biomes, ground layers, caves, etc.
        $this->generateRawTerrain($world, $chunk_x, $chunk_z);
    }

    public function populateChunk(ChunkManager $world, int $chunk_x, int $chunk_z) : void{
        // Intentionally no-op: do not run any populators/decorators.
    }
}
