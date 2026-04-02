<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class ZombieVillager extends Zombie{
	const NETWORK_ID = 44;

	public $width = 0.6;
	public $height = 1.9;

	public function initEntity(){
		$this->setMaxHealth(20);
		parent::initEntity();
	}
	public function getName() : string{
		return "Zombie Villager";
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = ZombieVillager::NETWORK_ID;
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motion->x;
		$pk->speedY = $this->motion->y;
		$pk->speedZ = $this->motion->z;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}