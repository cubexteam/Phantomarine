<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class IronGolem extends Animal{
	const NETWORK_ID = 20;

	public $width = 1.4;
	public $height = 2.9;

	public function initEntity(){
		$this->setMaxHealth(100);
		parent::initEntity();
	}
	public function getName(){
		return "Iron Golem";
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = self::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motion->x;
		$pk->speedY = $this->motion->y;
		$pk->speedZ = $this->motion->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
	public function getDrops(){
		$drops = [ItemItem::get(ItemItem::IRON_INGOT, 0, mt_rand(3, 5))];
		$drops[] = ItemItem::get(ItemItem::POPPY, 0, mt_rand(0, 2));
		return $drops;
	}
}