<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Minecart as MinecartEntity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class Minecart extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::MINECART, $meta, $count, "Minecart");
	}
	public function getMaxStackSize() : int{
		return 1;
	}
	public function onActivate(Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$minecart = new MinecartEntity($player->getLevel(), new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $block->getX()),
				new DoubleTag("", $block->getY() + 0.8),
				new DoubleTag("", $block->getZ())
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
		$minecart->spawnToAll();

		if($player->isSurvival()){
			$this->pop();
		}

		return true;
	}
}
