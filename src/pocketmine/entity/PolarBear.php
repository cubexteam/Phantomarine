<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class PolarBear extends Monster{
	const NETWORK_ID = 28;

	public $width = 1.3;
	public $height = 1.4;

	public $dropExp = [5, 5];
	public function getName(){
		return "Polar Bear";
	}

	public function initEntity(){
		$this->setMaxHealth(30);
		parent::initEntity();
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = PolarBear::NETWORK_ID;
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
		$drops = [ItemItem::get(ItemItem::RAW_SALMON, 0, mt_rand(0, 2))];
		$drops[] = ItemItem::get(ItemItem::RAW_FISH, 0, mt_rand(0, 2));
		return $drops;
	}
}