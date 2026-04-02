<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */


namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;

class Stray extends Skeleton{
	const NETWORK_ID = 46;

	public $width = 0.6;
	public $height = 1.9;

	public $dropExp = [5, 5];
	public function getName() : string{
		return "Stray";
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Stray::NETWORK_ID;
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

		Entity::spawnTo($player);

		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->item = new ItemItem(ItemItem::BOW);
		$pk->inventorySlot = 0;
		$pk->hotbarSlot = 0;

		$player->dataPacket($pk);
	}
}
