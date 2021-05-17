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
use pocketmine\world\World;

class NetherPopulator implements Populator{

	/** @var Populator[] */
	private array $in_ground_populators = [];

	/** @var Populator[] */
	private array $on_ground_populators = [];

	private OrePopulator $ore_populator;
	private FireDecorator $fire_decorator;
	private GlowstoneDecorator $glowstone_decorator_1;
	private GlowstoneDecorator $glowstone_decorator_2;
	private MushroomDecorator $brown_mushroom_decorator;
	private MushroomDecorator $red_mushroom_decorator;

	public function __construct(int $world_height = World::Y_MAX){
		$this->ore_populator = new OrePopulator($world_height);
		$this->in_ground_populators[] = $this->ore_populator;

		$this->fire_decorator = new FireDecorator();
		$this->glowstone_decorator_1 = new GlowstoneDecorator(true);
		$this->glowstone_decorator_2 = new GlowstoneDecorator();
		$this->brown_mushroom_decorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->red_mushroom_decorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());

		array_push($this->on_ground_populators,
			$this->fire_decorator,
			$this->glowstone_decorator_1,
			$this->glowstone_decorator_2,
			$this->fire_decorator,
			$this->brown_mushroom_decorator,
			$this->red_mushroom_decorator
		);

		$this->fire_decorator->setAmount(1);
		$this->glowstone_decorator_1->setAmount(1);
		$this->glowstone_decorator_2->setAmount(1);
		$this->brown_mushroom_decorator->setAmount(1);
		$this->red_mushroom_decorator->setAmount(1);
	}

	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		$this->populateInGround($world, $random, $chunk_x, $chunk_z, $chunk);
		$this->populateOnGround($world, $random, $chunk_x, $chunk_z, $chunk);
	}

	private function populateInGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		foreach($this->in_ground_populators as $populator){
			$populator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}

	private function populateOnGround(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk) : void{
		foreach($this->on_ground_populators as $populator){
			$populator->populate($world, $random, $chunk_x, $chunk_z, $chunk);
		}
	}
}