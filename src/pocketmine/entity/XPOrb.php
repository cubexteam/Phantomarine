<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\player\PlayerPickupExpOrbEvent;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class XPOrb extends Entity{
	const NETWORK_ID = 69;
	public const MAX_TARGET_DISTANCE = 8.0;
	public const ORB_SPLIT_SIZES = [2477, 1237, 617, 307, 149, 73, 37, 17, 7, 3, 1];

	public $width = 0.25;
	public $height = 0.25;

	protected $gravity = 0.04;
	protected $drag = 0.02;
	protected $age = 0;

	protected $experience = 0;

	protected $range = 6;
	protected $lookForTargetTime = 0;
	protected $targetPlayerRuntimeId = null;
	public static function getMaxOrbSize(int $amount) : int{
		foreach(self::ORB_SPLIT_SIZES as $split){
			if($amount >= $split){
				return $split;
			}
		}

		return 1;
	}
	public static function splitIntoOrbSizes(int $amount) : array{
		$result = [];

		while($amount > 0){
			$size = self::getMaxOrbSize($amount);
			$result[] = $size;
			$amount -= $size;
		}

		return $result;
	}

	public function initEntity(){
		parent::initEntity();
		if(isset($this->namedtag->Experience)){
			$this->experience = $this->namedtag["Experience"];
		}else $this->close();
	}

	public function hasTargetPlayer() : bool{
		return $this->targetPlayerRuntimeId !== null;
	}

	public function getTargetPlayer() : ?Human{
		if($this->targetPlayerRuntimeId === null){
			return null;
		}

		$entity = $this->level->getEntity($this->targetPlayerRuntimeId);
		if($entity instanceof Human){
			return $entity;
		}

		return null;
	}

	public function setTargetPlayer(?Human $player) : void{
		$this->targetPlayerRuntimeId = $player?->getId();
	}
	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->age += $tickDiff;
		if($this->age > 6000){
			$this->flagForDespawn();
			return true;
		}

		$currentTarget = $this->getTargetPlayer();
		if($currentTarget !== null and (!$currentTarget->isAlive() or $currentTarget->distanceSquared($this) > self::MAX_TARGET_DISTANCE ** 2)){
			$currentTarget = null;
		}

		if($this->lookForTargetTime >= 20){
			if($currentTarget === null){
				$newTarget = $this->level->getNearestEntity($this, self::MAX_TARGET_DISTANCE, Human::class);

				if($newTarget instanceof Human and !($newTarget instanceof Player and $newTarget->isSpectator())){
					$currentTarget = $newTarget;
				}
			}

			$this->lookForTargetTime = 0;
		}else{
			$this->lookForTargetTime += $tickDiff;
		}

		$this->setTargetPlayer($currentTarget);

		if($currentTarget !== null){
			$vector = $currentTarget->add(0, $currentTarget->getEyeHeight() / 2, 0)->subtract($this)->divide(self::MAX_TARGET_DISTANCE);

			$distance = $vector->lengthSquared();
			if($distance < 1){
				$diff = $vector->normalize()->multiply(0.2 * (1 - sqrt($distance)) ** 2);

				$this->motion->x += $diff->x;
				$this->motion->y += $diff->y;
				$this->motion->z += $diff->z;
			}

			if($this->getLevel()->getServer()->expEnabled and $currentTarget->canPickupXp() and $this->boundingBox->intersectsWith($currentTarget->getBoundingBox())){
				$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new PlayerPickupExpOrbEvent($currentTarget, $this->getExperience()));
				if(!$ev->isCancelled()){
					$this->flagForDespawn();
					if($this->getExperience() > 0){
						$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ORB, mt_rand());
						$currentTarget->addXp($this->getExperience());
						$currentTarget->resetXpCooldown();

					}
				}
			}
		}

		return $hasUpdate;
	}

	protected function tryChangeMovement(){
		$this->checkObstruction($this->x, $this->y, $this->z);
		parent::tryChangeMovement();
	}
	public function canCollideWith(Entity $entity){
		return false;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}
	public function setExperience($exp){
		$this->experience = $exp;
	}
	public function getExperience(){
		return $this->experience;
	}
	public function spawnTo(Player $player){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_NO_AI, true);
		$pk = new AddEntityPacket();
		$pk->type = XPOrb::NETWORK_ID;
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
