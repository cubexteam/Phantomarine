<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Chicken extends Animal{
	const NETWORK_ID = 10;

	public $width = 0.6;
	public $height = 0.8;

	public $dropExp = [1, 3];
	public function getName() : string{
		return "Chicken";
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Chicken::NETWORK_ID;
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
		$drops = [ItemItem::get(ItemItem::FEATHER, 0, mt_rand(0, 2))
		];
		$drops[] = ItemItem::get(ItemItem::RAW_CHICKEN, 0, 1);

		return $drops;
	}
}
