<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class EnderCrystal extends Entity implements Explosive{

	const NETWORK_ID = 71;

	public $height = 0.98;
	public $width = 0.98;
	public $gravity = 0.5;
	public $drag = 0.1;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->setMaxHealth(1);
		$this->setHealth(1);
	}
	public function getName() : string{
		return "Ender Crystal";
	}

	public function isFireProof(): bool{
		return true;
	}

	public function attack(EntityDamageEvent $source){
		parent::attack($source);
		if(
			$source->getCause() !== EntityDamageEvent::CAUSE_VOID &&
			!$this->isFlaggedForDespawn() &&
			!$source->isCancelled()
		){
			$this->flagForDespawn();
			$this->explode();
		}
	}

	public function explode(bool $dropItem = true){
		$this->server->getPluginManager()->callEvent($ev = new ExplosionPrimeEvent($this, 6, $dropItem));
		if(!$ev->isCancelled()){
			$explosion = new Explosion(Position::fromObject($this->add(0, $this->height / 2, 0), $this->level), $ev->getForce(), $this, $ev->dropItem());
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = self::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = 0;
		$pk->speedY = 0;
		$pk->speedZ = 0;
		$pk->yaw = 0;
		$pk->pitch = 0;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}