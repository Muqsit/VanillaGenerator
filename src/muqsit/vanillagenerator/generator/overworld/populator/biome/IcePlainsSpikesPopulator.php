<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\IceDecorator;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class IcePlainsSpikesPopulator extends IcePlainsPopulator{

	protected IceDecorator $ice_decorator;

	public function __construct(){
		parent::__construct();
		$this->tall_grass_decorator->setAmount(0);
		$this->ice_decorator = new IceDecorator();
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$this->ice_decorator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		parent::populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::ICE_PLAINS_SPIKES];
	}
}