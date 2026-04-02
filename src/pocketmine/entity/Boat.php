<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;

class Boat extends Vehicle{
	const NETWORK_ID = 90;

	public $height = 0.455;
	public $width = 1.4;

	public $gravity = 0.5;
	public $drag = 0.1;
	protected $age = 0;
	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->WoodID)){
			$nbt->WoodID = new IntTag("WoodID", 0);
		}
		parent::__construct($level, $nbt);
		$this->setDataProperty(self::DATA_VARIANT, self::DATA_TYPE_INT, $this->getWoodID());
	}
	public function getWoodID() : int{
		return (int) $this->namedtag["WoodID"];
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Boat::NETWORK_ID;
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
	public function attack(EntityDamageEvent $source){
		parent::attack($source);

		if(!$source->isCancelled()){
			$pk = new EntityEventPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->event = EntityEventPacket::HURT_ANIMATION;
			foreach($this->getLevel()->getPlayers() as $player){
				$player->dataPacket($pk);
			}
		}
	}
	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->level->getBlockAt($this->x, $this->y, $this->z)->getBoundingBox() == null or $this->isInsideOfWater()){
			$this->motion->y = 0.1;
		}else{
			$this->motion->y = -0.08;
		}

		$this->age += $tickDiff;
		if($this->linkedEntity == null or $this->linkedType = 0){
			if($this->age > 1500){
				$this->flagForDespawn();
				$hasUpdate = true;
			}
		}else{
			$this->age = 0;
		}

		return $hasUpdate;
	}
	public function getDrops(){
		return [
			ItemItem::get(ItemItem::BOAT, 0, 1)
		];
	}
	public function getSaveId(){
		$class = new \ReflectionClass(static::class);
		return $class->getShortName();
	}
}
