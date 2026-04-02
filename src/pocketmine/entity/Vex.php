<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Vex extends Monster{
	const NETWORK_ID = 105;

	public $width = 0.4;
	public $height = 0.8;

	public $dropExp = [5, 5];
	public function getName(){
		return "Vex";
	}

	public function initEntity(){
		$this->setMaxHealth(14);
		parent::initEntity();
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Vex::NETWORK_ID;
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
}