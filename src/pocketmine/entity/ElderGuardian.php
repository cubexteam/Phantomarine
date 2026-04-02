<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;


class ElderGuardian extends Animal{
	const NETWORK_ID = 50;

	public $width = 1.9975;
	public $height = 1.9975;

	public $dropExp = [5, 5];
	public function getName() : string{
		return "Elder Guardian";
	}

	public function initEntity(){
		$this->setMaxHealth(80);
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ELDER, true);
		parent::initEntity();
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = ElderGuardian::NETWORK_ID;
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
			ItemItem::get(ItemItem::PRISMARINE_CRYSTALS, 0, mt_rand(0, 1))
		];
		$drops[] = ItemItem::get(ItemItem::PRISMARINE_SHARD, 0, mt_rand(0, 2));

		return $drops;
	}
}
