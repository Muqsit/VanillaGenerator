<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\populator\biome\BiomePopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\BirchForestMountainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\BirchForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\DesertMountainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\DesertPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\FlowerForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\ForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\IcePlainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\IcePlainsSpikesPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\JungleEdgePopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\JunglePopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\MegaSpruceTaigaPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\MegaTaigaPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\PlainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\RoofedForestPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\SavannaMountainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\SavannaPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\SunflowerPlainsPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\SwamplandPopulator;
use muqsit\vanillagenerator\generator\overworld\populator\biome\TaigaPopulator;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use ReflectionClass;
use function array_key_exists;

class OverworldPopulator implements Populator{

	/** @var Populator[] */
	private array $biome_populators = []; // key = biomeId

	/**
	 * Creates a populator with biome populators for all vanilla overworld biomes.
	 */
	public function __construct(){
		$this->registerBiomePopulator(new BiomePopulator()); // defaults applied to all biomes
		$this->registerBiomePopulator(new PlainsPopulator());
		$this->registerBiomePopulator(new SunflowerPlainsPopulator());
		$this->registerBiomePopulator(new ForestPopulator());
		$this->registerBiomePopulator(new BirchForestPopulator());
		$this->registerBiomePopulator(new BirchForestMountainsPopulator());
		$this->registerBiomePopulator(new RoofedForestPopulator());
		$this->registerBiomePopulator(new FlowerForestPopulator());
		$this->registerBiomePopulator(new DesertPopulator());
		$this->registerBiomePopulator(new DesertMountainsPopulator());
		$this->registerBiomePopulator(new JunglePopulator());
		$this->registerBiomePopulator(new JungleEdgePopulator());
		$this->registerBiomePopulator(new SwamplandPopulator());
		$this->registerBiomePopulator(new TaigaPopulator());
		$this->registerBiomePopulator(new MegaTaigaPopulator());
		$this->registerBiomePopulator(new MegaSpruceTaigaPopulator());
		$this->registerBiomePopulator(new IcePlainsPopulator());
		$this->registerBiomePopulator(new IcePlainsSpikesPopulator());
		$this->registerBiomePopulator(new SavannaPopulator());
		$this->registerBiomePopulator(new SavannaMountainsPopulator());
		/*
		$this->registerBiomePopulator(new ExtremeHillsPopulator());
		$this->registerBiomePopulator(new ExtremeHillsPlusPopulator());
		$this->registerBiomePopulator(new MesaPopulator());
		$this->registerBiomePopulator(new MesaForestPopulator());
		$this->registerBiomePopulator(new MushroomIslandPopulator());
		$this->registerBiomePopulator(new OceanPopulator());
		*/
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$biome = $chunk->getBiomeId(8, 8, 8);
		if(array_key_exists($biome, $this->biome_populators)){
			$this->biome_populators[$biome]->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}

	private function registerBiomePopulator(BiomePopulator $populator) : void{
		$biomes = $populator->getBiomes();
		if($biomes === null){
			$biomes = array_values((new ReflectionClass(BiomeIds::class))->getConstants());
		}

		foreach($biomes as $biome){
			$this->biome_populators[$biome] = $populator;
		}
	}
}