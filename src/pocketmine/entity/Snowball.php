<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\particle\SnowballPoofParticle;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Snowball extends Throwable{
	const NETWORK_ID = 81;

	protected function onHit(ProjectileHitEvent $event) : void{
		for($i = 0; $i < 6; ++$i){
			$this->level->addParticle(new SnowballPoofParticle($this));
		}
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = Snowball::NETWORK_ID;
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