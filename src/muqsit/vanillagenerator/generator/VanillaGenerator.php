<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use GlobalLogger;
use Logger;
use muqsit\vanillagenerator\generator\biomegrid\BiomeEdgeMapLayer;
use muqsit\vanillagenerator\generator\biomegrid\BiomeThinEdgeMapLayer;
use muqsit\vanillagenerator\generator\biomegrid\BiomeVariationMapLayer;
use muqsit\vanillagenerator\generator\biomegrid\MapLayer;
use muqsit\vanillagenerator\generator\biomegrid\utils\MapLayerPair;
use muqsit\vanillagenerator\generator\biomegrid\WhittakerMapLayer;
use muqsit\vanillagenerator\generator\nether\decorator\MushroomDecorator;
use muqsit\vanillagenerator\generator\noise\glowstone\SimplexNoise;
use muqsit\vanillagenerator\generator\object\IceSpike;
use muqsit\vanillagenerator\generator\object\Lake;
use muqsit\vanillagenerator\generator\object\StoneBoulder;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use muqsit\vanillagenerator\generator\overworld\biome\BiomeHeightManager;
use muqsit\vanillagenerator\generator\overworld\decorator\IceDecorator;
use muqsit\vanillagenerator\generator\overworld\OverworldGenerator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\BiomePopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\BirchForestMountainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\BirchForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\FlowerForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\ForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\IcePlainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\JungleEdgePopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\JunglePopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\MegaSpruceTaigaPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\MegaTaigaPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\PlainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\RoofedForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\SavannaPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\SunflowerPlainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\SwamplandPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\TaigaPopulator;
use muqsit\vanillagenerator\generator\overworld\WorldType;
use muqsit\vanillagenerator\generator\utils\preset\GeneratorPreset;
use muqsit\vanillagenerator\generator\utils\WorldOctaves;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\World;
use PrefixedLogger;

/**
 * @template T of WorldOctaves
 */
abstract class VanillaGenerator extends Generator{

	public static function logger() : Logger{
		static $logger = new PrefixedLogger(GlobalLogger::get(), "VanillaGenerator");
		return $logger;
	}

	private static function init() : void{
		static $init = false;
		if($init){
			return;
		}
		MushroomDecorator::init();
		BiomeClimateManager::init();
		BiomeHeightManager::init();
		RoofedForestPopulator::init();
		SunflowerPlainsPopulator::init();
		FlowerForestPopulator::init();
		SavannaPopulator::init();
		JunglePopulator::init();
		JungleEdgePopulator::init();
		PlainsPopulator::init();
		MegaTaigaPopulator::init();
		BirchForestMountainsPopulator::init();
		BiomePopulator::init();
		MegaSpruceTaigaPopulator::init();
		TaigaPopulator::init();
		ForestPopulator::init();
		BirchForestPopulator::init();
		IcePlainsPopulator::init();
		SwamplandPopulator::init();
		IceDecorator::init();
		BiomeEdgeMapLayer::init();
		BiomeThinEdgeMapLayer::init();
		BiomeVariationMapLayer::init();
		WhittakerMapLayer::init();
		SimplexNoise::init();
		IceSpike::init();
		Lake::init();
		StoneBoulder::init();
		OverworldGenerator::init();
		$init = true;
	}

	/** @var T */
	private ?WorldOctaves $octave_cache = null;

	/** @var Populator[] */
	private array $populators = [];

	private MapLayerPair $biome_grid;

	public function __construct(int $seed, int $environment, ?string $world_type, GeneratorPreset $preset){
		parent::__construct($seed, $preset->toString());
		$this->biome_grid = MapLayer::initialize($seed, $environment, $world_type ?? WorldType::NORMAL);
		self::init();
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $size_x
	 * @param int $size_z
	 * @return int[]
	 */
	public function getBiomeGridAtLowerRes(int $x, int $z, int $size_x, int $size_z) : array{
		return $this->biome_grid->low_resolution->generateValues($x, $z, $size_x, $size_z);
	}

	/**
	 * @param int $x
	 * @param int $z
	 * @param int $size_x
	 * @param int $size_z
	 * @return int[]
	 */
	public function getBiomeGrid(int $x, int $z, int $size_x, int $size_z) : array{
		return $this->biome_grid->high_resolution->generateValues($x, $z, $size_x, $size_z);
	}

	protected function addPopulators(Populator ...$populators) : void{
		array_push($this->populators, ...$populators);
	}

	/**
	 * @return T
	 */
	abstract protected function createWorldOctaves() : WorldOctaves;

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$biomes = new VanillaBiomeGrid();
		$biome_values = $this->biome_grid->high_resolution->generateValues($chunkX * 16, $chunkZ * 16, 16, 16);
		foreach($biome_values as $i => $id){
			$biomes->setBiome($i & 15, $i >> 4, $id);
		}

		$this->generateChunkData($world, $chunkX, $chunkZ, $biomes);
	}

	abstract protected function generateChunkData(ChunkManager $world, int $chunk_x, int $chunk_z, VanillaBiomeGrid $biomes) : void;

	/**
	 * @return T
	 */
	final protected function getWorldOctaves() : WorldOctaves{
		return $this->octave_cache ??= $this->createWorldOctaves();
	}

	/**
	 * @return Populator[]
	 */
	public function getDefaultPopulators() : array{
		return $this->populators;
	}

	public function populateChunk(ChunkManager $world, int $chunk_x, int $chunk_z) : void{
		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunk_x, $chunk_z);
		foreach($this->populators as $populator){
			$populator->populate($world, $this->random, $chunk_x, $chunk_z, $chunk);
		}
	}

	public function getMaxY() : int{
		return World::Y_MAX;
	}
}