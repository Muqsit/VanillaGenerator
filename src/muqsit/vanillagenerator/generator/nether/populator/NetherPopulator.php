<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether\populator;

use muqsit\vanillagenerator\generator\nether\decorator\FireDecorator;
use muqsit\vanillagenerator\generator\nether\decorator\GlowstoneDecorator;
use muqsit\vanillagenerator\generator\nether\decorator\MushroomDecorator;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class NetherPopulator implements Populator{

	/** @var Populator[] */
	private $inGroundPopulators = [];

	/** @var Populator[] */
	private $onGroundPopulators = [];

	/** @var OrePopulator */
	private $orePopulator;

	/** @var FireDecorator */
	private $fireDecorator;

	/** @var GlowstoneDecorator */
	private $glowstoneDecorator1;

	/** @var GlowstoneDecorator */
	private $glowstoneDecorator2;

	/** @var MushroomDecorator */
	private $brownMushroomDecorator;

	/** @var MushroomDecorator */
	private $redMushroomDecorator;

	public function __construct(){
		$this->orePopulator = new OrePopulator();
		$this->inGroundPopulators[] = $this->orePopulator;

		$this->fireDecorator = new FireDecorator();
		$this->glowstoneDecorator1 = new GlowstoneDecorator(true);
		$this->glowstoneDecorator2 = new GlowstoneDecorator();
		$this->brownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->redMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());

		array_push($this->onGroundPopulators,
			$this->fireDecorator,
			$this->glowstoneDecorator1,
			$this->glowstoneDecorator2,
			$this->fireDecorator,
			$this->brownMushroomDecorator,
			$this->redMushroomDecorator
		);

		$this->fireDecorator->setAmount(1);
		$this->glowstoneDecorator1->setAmount(1);
		$this->glowstoneDecorator2->setAmount(1);
		$this->brownMushroomDecorator->setAmount(1);
		$this->redMushroomDecorator->setAmount(1);
	}

	public function populate(ChunkManager $world, Random $random, Chunk $chunk) : void{
		$this->populateInGround($world, $random, $chunk);
		$this->populateOnGround($world, $random, $chunk);
	}

	private function populateInGround(ChunkManager $world, Random $random, Chunk $chunk) : void{
		foreach($this->inGroundPopulators as $populator){
			$populator->populate($world, $random, $chunk);
		}
	}

	private function populateOnGround(ChunkManager $world, Random $random, Chunk $chunk) : void{
		foreach($this->onGroundPopulators as $populator){
			$populator->populate($world, $random, $chunk);
		}
	}
}