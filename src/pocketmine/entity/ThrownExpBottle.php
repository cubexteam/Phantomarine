<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\particle\SpellParticle;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class ThrownExpBottle extends Throwable{
	const NETWORK_ID = 68;

	public function onHit(ProjectileHitEvent $event) : void{
		$this->getLevel()->addParticle(new SpellParticle($this->add(0, 0.01), 46, 82, 153));
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_GLASS);

		$this->getLevel()->spawnXPOrb($this, mt_rand(3, 11));
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = ThrownExpBottle::NETWORK_ID;
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