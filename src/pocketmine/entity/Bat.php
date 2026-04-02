<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Bat extends FlyingAnimal{

	const NETWORK_ID = 19;

	const DATA_IS_RESTING = 16;

	public $width = 0.5;
	public $height = 0.9;

	public $flySpeed = 0.8;
	public $switchDirectionTicks = 100;
	protected $age = 0;
	public function getName() : string{
		return "Bat";
	}

	public function initEntity(){
		$this->setMaxHealth(6);
		parent::initEntity();
	}
	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->isResting)){
			$nbt->isResting = new ByteTag("isResting", 0);
		}
		parent::__construct($level, $nbt);

		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RESTING, $this->isResting());
	}
	public function isResting() : int{
		return (int) $this->namedtag["isResting"];
	}
	public function setResting(bool $resting){
		$this->namedtag->isResting = new ByteTag("isResting", $resting ? 1 : 0);
	}
	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);

		$this->age += $tickDiff;
		if($this->age > 12000){
			$this->flagForDespawn();
			return true;
		}

		return $hasUpdate;
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Bat::NETWORK_ID;
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