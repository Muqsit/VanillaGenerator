<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\nether\populator;

use muqsit\vanillagenerator\generator\object\OreType;
use muqsit\vanillagenerator\generator\overworld\populator\biome\OrePopulator as OverworldOrePopulator;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;

class OrePopulator extends OverworldOrePopulator{

	/**
	 * @noinspection MagicMethodsValidityInspection
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(){
		$this->addOre(new OreType(VanillaBlocks::NETHER_QUARTZ_ORE(), 10, 118, 13, BlockLegacyIds::NETHERRACK), 16);
		$this->addOre(new OreType(VanillaBlocks::MAGMA(), 26, 37, 32, BlockLegacyIds::NETHERRACK), 16);
	}
}