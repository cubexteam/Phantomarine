<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\Rail;
use pocketmine\entity\MinecartTNT as MinecartTNTEntity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class MinecartTNT extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::MINECART_WITH_TNT, $meta, $count, 'Minecart TNT');
	}
	public function getMaxStackSize() : int{
		return 1;
	}
	public function onActivate(Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		if($target instanceof Rail){
			$entity = new MinecartTNTEntity($player->getLevel(), new CompoundTag('', [
				new ListTag('Pos', [new DoubleTag('', $block->getX()), new DoubleTag('', $block->getY()), new DoubleTag('', $block->GetZ())]),
				new ListTag('Rotation', [new DoubleTag('', 0), new DoubleTag('', 0)])]));
			$entity->spawnToAll();
		}
		if($player instanceof Player){
			if($player->isSurvival()){
				$player->getInventory()->setItemInHand(Item::get(0));
			}
		}
		return true;
	}
}