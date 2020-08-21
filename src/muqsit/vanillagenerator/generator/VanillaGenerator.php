<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use muqsit\vanillagenerator\generator\biomegrid\MapLayer;
use muqsit\vanillagenerator\generator\overworld\WorldType;
use muqsit\vanillagenerator\generator\utils\WorldOctaves;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\SimpleChunkManager;
use pocketmine\world\World;
use ReflectionProperty;

abstract class VanillaGenerator extends Generator{

	protected const WORLD_DEPTH = 128;

	private static function modifyChunkManager(SimpleChunkManager $world, self $generator) : SimpleChunkManager{
		static $_worldHeight = null;
		if($_worldHeight === null){
			/** @noinspection PhpUnhandledExceptionInspection */
			$_worldHeight = new ReflectionProperty($world, "worldHeight");
			$_worldHeight->setAccessible(true);
		}

		$_worldHeight->setValue($world, $generator->getWorldHeight());
		return $world;
	}

	/** @var WorldOctaves|null */
	private $octaveCache = null;

	/** @var Populator[] */
	private $populators = [];

	/** @var MapLayer[] */
	private $biomeGrid;

	public function __construct(ChunkManager $world, int $seed, int $environment, ?string $world_type = null, array $options = []){
		assert($world instanceof SimpleChunkManager);
		parent::__construct(self::modifyChunkManager($world, $this), $seed, $options);
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

	public function generateChunk(int $chunkX, int $chunkZ) : void{
		$biomes = new VanillaBiomeGrid();
		$biomeValues = $this->biomeGrid[0]->generateValues($chunkX * 16, $chunkZ * 16, 16, 16);
		for($i = 0, $biomeValues_c = count($biomeValues); $i < $biomeValues_c; ++$i){
			$biomes->biomes[$i] = $biomeValues[$i];
		}

		$this->generateChunkData($chunkX, $chunkZ, $biomes);
	}

	abstract protected function generateChunkData(int $chunkX, int $chunkZ, VanillaBiomeGrid $biomes) : void;

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

	final public function populateChunk(int $chunkX, int $chunkZ) : void{
		/** @var Chunk $chunk */
		$chunk = $this->world->getChunk($chunkX, $chunkZ);
		foreach($this->populators as $populator){
			$populator->populate($this->world, $this->random, $chunk);
		}
	}

	public function getWorldHeight() : int{
		return World::Y_MAX;
	}
}