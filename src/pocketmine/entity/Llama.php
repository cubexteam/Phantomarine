<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Llama extends Animal{
	const NETWORK_ID = 29;

	const CREAMY = 0;
	const WHITE = 1;
	const BROWN = 2;
	const GRAY = 3;

	public $width = 0.9;
	public $height = 1.87;

	public $dropExp = [1, 3];
	public function getName(){
		return "Llama";
	}

	public function initEntity(){
		$this->setMaxHealth(30);
		$this->setDataProperty(Entity::DATA_VARIANT, Entity::DATA_TYPE_INT, rand(0, 3));
		parent::initEntity();
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
		$drops = [
			ItemItem::get(ItemItem::LEATHER, 0, mt_rand(0, 2))
		];

		return $drops;
	}
}
