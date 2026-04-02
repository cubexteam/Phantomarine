<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\particle\EndermanTeleportParticle;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class EnderPearl extends Throwable{

	const NETWORK_ID = 87;

	protected function onHit(ProjectileHitEvent $event) : void{
		$owner = $this->getOwningEntity();
		if($owner !== null){
			if ($owner->getLevel() === $this->getLevel()) {
				$this->getLevel()->addParticle(new EndermanTeleportParticle($owner->asVector3()), $this->getViewers());
				$this->getLevel()->addSound(new EndermanTeleportSound($owner->asVector3()), $this->getViewers());
				$owner->teleport($event->getRayTraceResult()->getHitVector(), $owner->getYaw(), $owner->getPitch());
				$this->getLevel()->addSound(new EndermanTeleportSound($owner->asVector3()), $this->getViewers());

				$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
			}
		}
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = EnderPearl::NETWORK_ID;
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
