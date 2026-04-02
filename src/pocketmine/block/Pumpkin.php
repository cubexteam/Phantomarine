<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\IronGolem;
use pocketmine\entity\SnowGolem;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class Pumpkin extends Solid{

	protected $id = self::PUMPKIN;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 1;
	}
	public function isHelmet(){
		return true;
	}
	public function getToolType(){
		return Tool::TYPE_AXE;
	}
	public function getName() : string{
		return "Pumpkin";
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$this->meta = ((int) $player->getDirection() + 5) % 4;
		}
		$this->getLevel()->setBlock($block, $this, true, true);
		if($player != null){
			$level = $this->getLevel();
			if($player->getServer()->allowSnowGolem){
				$block0 = $level->getBlock($block->add(0, -1, 0));
				$block1 = $level->getBlock($block->add(0, -2, 0));
				if($block0->getId() == Item::SNOW_BLOCK and $block1->getId() == Item::SNOW_BLOCK){
					$level->setBlock($block, BlockFactory::get(Block::AIR));
					$level->setBlock($block0, BlockFactory::get(Block::AIR));
					$level->setBlock($block1, BlockFactory::get(Block::AIR));
					$golem = new SnowGolem($player->getLevel(), new CompoundTag("", [
						new ListTag("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y),
							new DoubleTag("", $this->z)
						]),
						new ListTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						new ListTag("Rotation", [
							new FloatTag("", 0),
							new FloatTag("", 0)
						]),
					]));
					$golem->spawnToAll();
				}
			}
			if($player->getServer()->allowIronGolem){
				$block0 = $level->getBlock($block->add(0, -1, 0));
				$block1 = $level->getBlock($block->add(0, -2, 0));
				$block2 = $level->getBlock($block->add(-1, -1, 0));
				$block3 = $level->getBlock($block->add(1, -1, 0));
				$block4 = $level->getBlock($block->add(0, -1, -1));
				$block5 = $level->getBlock($block->add(0, -1, 1));
				if($block0->getId() == Item::IRON_BLOCK and $block1->getId() == Item::IRON_BLOCK){
					if($block2->getId() == Item::IRON_BLOCK and $block3->getId() == Item::IRON_BLOCK and $block4->getId() == Item::AIR and $block5->getId() == Item::AIR){
						$level->setBlock($block2, BlockFactory::get(Block::AIR));
						$level->setBlock($block3, BlockFactory::get(Block::AIR));
					}elseif($block4->getId() == Item::IRON_BLOCK and $block5->getId() == Item::IRON_BLOCK and $block2->getId() == Item::AIR and $block3->getId() == Item::AIR){
						$level->setBlock($block4, BlockFactory::get(Block::AIR));
						$level->setBlock($block5, BlockFactory::get(Block::AIR));
					}else return false;
					$level->setBlock($block, BlockFactory::get(Block::AIR));
					$level->setBlock($block0, BlockFactory::get(Block::AIR));
					$level->setBlock($block1, BlockFactory::get(Block::AIR));
					$golem = new IronGolem($player->getLevel(), new CompoundTag("", [
						new ListTag("Pos", [
							new DoubleTag("", $this->x),
							new DoubleTag("", $this->y),
							new DoubleTag("", $this->z)
						]),
						new ListTag("Motion", [
							new DoubleTag("", 0),
							new DoubleTag("", 0),
							new DoubleTag("", 0)
						]),
						new ListTag("Rotation", [
							new FloatTag("", 0),
							new FloatTag("", 0)
						]),
					]));
					$golem->spawnToAll();
				}
			}
		}

		return true;
	}

}
