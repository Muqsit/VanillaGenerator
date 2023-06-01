<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use muqsit\vanillagenerator\generator\object\TerrainObject;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wood;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use function array_key_exists;

class GenericTree extends TerrainObject{

	protected BlockTransaction $transaction;
	protected int $height;
	protected Wood $log_type;
	protected Leaves $leaves_type;

	/** @var int[] */
	protected array $overridables;

	/**
	 * Initializes this tree with a random height, preparing it to attempt to generate.
	 *
	 * @param Random $random the PRNG
	 * @param BlockTransaction $transaction the BlockTransaction used to check for space and to fill in wood and leaves
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		$this->transaction = $transaction;
		$this->setOverridables(
			BlockTypeIds::AIR,
			BlockTypeIds::ACACIA_LEAVES,
			BlockTypeIds::BIRCH_LEAVES,
			BlockTypeIds::DARK_OAK_LEAVES,
			BlockTypeIds::JUNGLE_LEAVES,
			BlockTypeIds::OAK_LEAVES,
			BlockTypeIds::SPRUCE_LEAVES,
			BlockTypeIds::GRASS,
			BlockTypeIds::DIRT,
			BlockTypeIds::ACACIA_WOOD,
			BlockTypeIds::BIRCH_WOOD,
			BlockTypeIds::DARK_OAK_WOOD,
			BlockTypeIds::JUNGLE_WOOD,
			BlockTypeIds::OAK_WOOD,
			BlockTypeIds::SPRUCE_WOOD,
			BlockTypeIds::ACACIA_SAPLING,
			BlockTypeIds::BIRCH_SAPLING,
			BlockTypeIds::DARK_OAK_SAPLING,
			BlockTypeIds::JUNGLE_SAPLING,
			BlockTypeIds::OAK_SAPLING,
			BlockTypeIds::SPRUCE_SAPLING,
			BlockTypeIds::VINES
		);
		$this->setHeight($random->nextBoundedInt(3) + 4);
		$this->setType(VanillaBlocks::OAK_LOG(), VanillaBlocks::OAK_LEAVES());
	}

	public function getLogType() : Wood{
		return clone $this->log_type;
	}

	public function getLeavesType() : Leaves{
		return clone $this->leaves_type;
	}

	final protected function setOverridables(int ...$overridables) : void{
		$this->overridables = array_flip($overridables);
	}

	final protected function setHeight(int $height) : void{
		$this->height = $height;
	}

	/**
	 * Sets the block data values for this tree's blocks.
	 */
	final protected function setType(Wood $log_type, Leaves $leaves_type) : void{
		$this->log_type = $log_type;
		$this->leaves_type = $leaves_type;
	}

	/**
	 * Checks whether this tree fits under the upper world limit.
	 * @param int $base_height the height of the base of the trunk
	 *
	 * @return bool whether this tree can grow without exceeding block height 255; false otherwise.
	 */
	public function canHeightFit(int $base_height) : bool{
		return $base_height >= 1 && $base_height + $this->height + 1 < World::Y_MAX;
	}

	/**
	 * Checks whether this tree can grow on top of the given block.
	 * @param Block $soil the block we're growing on
	 * @return bool whether this tree can grow on the type of block below it; false otherwise
	 */
	public function canPlaceOn(Block $soil) : bool{
		$type = $soil->getTypeId();
		return $type === BlockTypeIds::GRASS || $type === BlockTypeIds::DIRT || $type === BlockTypeIds::FARMLAND;
	}

	/**
	 * Checks whether this tree has enough space to grow.
	 *
	 * @param int $base_x the X coordinate of the base of the trunk
	 * @param int $base_y the Y coordinate of the base of the trunk
	 * @param int $base_z the Z coordinate of the base of the trunk
	 * @param ChunkManager $world the world to grow in
	 * @return bool whether this tree has space to grow
	 */
	public function canPlace(int $base_x, int $base_y, int $base_z, ChunkManager $world) : bool{
		for($y = $base_y; $y <= $base_y + 1 + $this->height; ++$y){
			// Space requirement
			$radius = match(true){
				$y === $base_y => 0, // radius at source block y is 0 (only trunk)
				$y >= $base_y + 1 + $this->height - 2 => 2, // max radius starting at leaves bottom
				default => 1 // default radius if above first block
			};
			// check for block collision on horizontal slices
			$height = $world->getMaxY();
			for($x = $base_x - $radius; $x <= $base_x + $radius; ++$x){
				for($z = $base_z - $radius; $z <= $base_z + $radius; ++$z){
					if($y >= 0 && $y < $height){
						// we can overlap some blocks around
						if(!array_key_exists($world->getBlockAt($x, $y, $z)->getTypeId(), $this->overridables)){
							return false;
						}
					}else{ // height out of range
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * Attempts to grow this tree at its current location. If successful, the associated {@link
	 * BlockStateDelegate} is instructed to set blocks to wood and leaves.
	 *
	 * @param ChunkManager $world
	 * @param Random $random
	 * @param int $source_x
	 * @param int $source_y
	 * @param int $source_z
	 * @return bool whether successfully grown
	 */
	public function generate(ChunkManager $world, Random $random, int $source_x, int $source_y, int $source_z) : bool{
		if($this->cannotGenerateAt($source_x, $source_y, $source_z, $world)){
			return false;
		}

		// generate the leaves
		for($y = $source_y + $this->height - 3; $y <= $source_y + $this->height; ++$y){
			$n = $y - ($source_y + $this->height);
			$radius = (int) (1 - $n / 2);
			for($x = $source_x - $radius; $x <= $source_x + $radius; ++$x){
				for($z = $source_z - $radius; $z <= $source_z + $radius; ++$z){
					if(abs($x - $source_x) !== $radius
						|| abs($z - $source_z) !== $radius
						|| ($random->nextBoolean() && $n !== 0)
					){
						$this->replaceIfAirOrLeaves($x, $y, $z, $this->leaves_type, $world);
					}
				}
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height; ++$y){
			$this->replaceIfAirOrLeaves($source_x, $source_y + $y, $source_z, $this->log_type, $world);
		}

		// block below trunk is always dirt
		$dirt = VanillaBlocks::DIRT();
		$this->transaction->addBlockAt($source_x, $source_y - 1, $source_z, $dirt);
		return true;
	}

	/**
	 * Returns whether any of {@link #canHeightFit(int)}, {@link #canPlace(int, int, int, World)} or
	 * {@link #canPlaceOn(BlockState)} prevent this tree from generating.
	 *
	 * @param int $base_x the X coordinate of the base of the trunk
	 * @param int $base_y the Y coordinate of the base of the trunk
	 * @param int $base_z the Z coordinate of the base of the trunk
	 * @param ChunkManager $world the world to grow in
	 * @return bool whether any of the checks prevent us from generating, false otherwise
	 */
	protected function cannotGenerateAt(int $base_x, int $base_y, int $base_z, ChunkManager $world) : bool{
		return !$this->canHeightFit($base_y)
			|| !$this->canPlaceOn($world->getBlockAt($base_x, $base_y - 1, $base_z))
			|| !$this->canPlace($base_x, $base_y, $base_z, $world);
	}

	/**
	 * Replaces the block at a location with the given new one, if it is air or leaves.
	 *
	 * @param int $x the x coordinate
	 * @param int $y the y coordinate
	 * @param int $z the z coordinate
	 * @param Block $new_material the new block type
	 * @param ChunkManager $world the world we are generating in
	 */
	protected function replaceIfAirOrLeaves(int $x, int $y, int $z, Block $new_material, ChunkManager $world) : void{
		$old_material = $world->getBlockAt($x, $y, $z);
		if($old_material->getTypeId() === BlockTypeIds::AIR || $old_material instanceof Leaves){
			$this->transaction->addBlockAt($x, $y, $z, $new_material);
		}
	}
}