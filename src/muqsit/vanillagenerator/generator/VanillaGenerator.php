<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use muqsit\vanillagenerator\generator\biomegrid\MapLayer;
use muqsit\vanillagenerator\generator\overworld\WorldType;
use muqsit\vanillagenerator\generator\utils\WorldOctaves;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\World;

abstract class VanillaGenerator extends Generator{

	/** @var WorldOctaves|null */
	private $octaveCache = null;

	/** @var Populator[] */
	private $populators = [];

	/** @var MapLayer[] */
	private $biomeGrid;

	public function __construct(int $seed, int $environment, ?string $world_type = null, array $options = []){
		parent::__construct($seed, $options);
		$this->biomeGrid = MapLayer::initialize($seed, $environment, $world_type ?? WorldType::NORMAL);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $sizeX
	 * @param int $sizeZ
	 * @return int[]
	 */
	public function getBiomeGridAtLowerRes(int $x, int $z, int $sizeX, int $sizeZ) : array{
		return $this->biomeGrid[1]->generateValues($x, $z, $sizeX, $sizeZ);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $sizeX
	 * @param int $sizeZ
	 * @return int[]
	 */
	public function getBiomeGrid(int $x, int $z, int $sizeX, int $sizeZ) : array{
		return $this->biomeGrid[0]->generateValues($x, $z, $sizeX, $sizeZ);
	}

	protected function addPopulators(Populator ...$populators) : void{
		array_push($this->populators, ...$populators);
	}

	/**
	 * @return WorldOctaves
	 */
	abstract protected function createWorldOctaves();

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$biomes = new VanillaBiomeGrid();
		$biomeValues = $this->biomeGrid[0]->generateValues($chunkX * 16, $chunkZ * 16, 16, 16);
		for($i = 0, $biomeValues_c = count($biomeValues); $i < $biomeValues_c; ++$i){
			$biomes->biomes[$i] = $biomeValues[$i];
		}

		$this->generateChunkData($world, $chunkX, $chunkZ, $biomes);
	}

	abstract protected function generateChunkData(ChunkManager $world, int $chunkX, int $chunkZ, VanillaBiomeGrid $biomes) : void;

	/**
	 * @return WorldOctaves
	 */
	protected function getWorldOctaves() : WorldOctaves{
		return $this->octaveCache ?? $this->octaveCache = $this->createWorldOctaves();
	}

	/**
	 * @return Populator[]
	 */
	public function getDefaultPopulators() : array{
		return $this->populators;
	}

	final public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunkX, $chunkZ);
		foreach($this->populators as $populator){
			$populator->populate($world, $this->random, $chunkX, $chunkZ, $chunk);
		}
	}

	public function getWorldHeight() : int{
		return World::Y_MAX;
	}
}