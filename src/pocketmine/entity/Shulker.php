<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Shulker extends Monster{
	const NETWORK_ID = 54;

	public $width = 1;
	public $height = 1;

	public $dropExp = [5, 5];
	public function getName() : string{
		return "Shulker";
	}

	public function initEntity(){
		$this->setMaxHealth(30);
		$this->setDataProperty(Entity::DATA_VARIANT, Entity::DATA_TYPE_INT, 10);
		parent::initEntity();
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Shulker::NETWORK_ID;
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
		$drops = [
			ItemItem::get(ItemItem::SHULKER_SHELL, 0, mt_rand(0, 1))
		];

		return $drops;
	}
}
