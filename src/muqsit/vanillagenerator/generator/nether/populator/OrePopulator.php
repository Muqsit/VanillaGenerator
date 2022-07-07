<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether\populator;

use muqsit\vanillagenerator\generator\object\OreType;
use muqsit\vanillagenerator\generator\overworld\populator\biome\OrePopulator as OverworldOrePopulator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\World;

class OrePopulator extends OverworldOrePopulator{

	/**
	 * @noinspection MagicMethodsValidityInspection
	 * @noinspection PhpMissingParentConstructorInspection
	 * @param int $world_height
	 */
	public function __construct(int $world_height = World::Y_MAX){
		$this->addOre(new OreType(VanillaBlocks::NETHER_QUARTZ_ORE(), 10, $world_height - (10 * ($world_height >> 7)), 13, BlockTypeIds::NETHERRACK), 16);
		$this->addOre(new OreType(VanillaBlocks::MAGMA(), 26, 32 + (5 * ($world_height >> 7)), 32, BlockTypeIds::NETHERRACK), 16);
	}
}