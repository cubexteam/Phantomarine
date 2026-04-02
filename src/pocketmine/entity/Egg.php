<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\particle\ItemBreakParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Egg extends Projectile{
	const NETWORK_ID = 82;

	public $width = 0.25;
	public $height = 0.25;

	protected $gravity = 0.03;
	protected $drag = 0.01;
	protected $age = 0;
	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($level, $nbt, $shootingEntity);
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		for($i = 0; $i < 6; ++$i){
			$this->level->addParticle(new ItemBreakParticle($this, Item::get(Item::EGG)));
		}
	}
	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->age += $tickDiff;
		if($this->age > 1200 or $this->isCollided){
			$this->flagForDespawn();
			$hasUpdate = true;
		}

		return $hasUpdate;
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = Egg::NETWORK_ID;
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
