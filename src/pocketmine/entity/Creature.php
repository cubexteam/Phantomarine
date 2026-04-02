<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

abstract class Creature extends Living{
	public $attackingTick = 0;
	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if(!$this instanceof Human){
			if($this->attackingTick > 0){
				$this->attackingTick--;
			}
			if(!$this->isAlive() and $this->hasSpawned){
				$this->deadTicks += $tickDiff;
				if($this->deadTicks >= 20){
					$this->despawnFromAll();
				}
				return true;
			}
		}

		return parent::entityBaseTick($tickDiff, $EnchantL);
	}
	public function willMove($distance = 36){
		foreach($this->getViewers() as $viewer){
			if($this->distance($viewer->getLocation()) <= $distance) return true;
		}
		return false;
	}
	public function attack(EntityDamageEvent $source){
		parent::attack($source);
		if(!$source->isCancelled() and $source->getCause() == EntityDamageEvent::CAUSE_ENTITY_ATTACK){
			$this->attackingTick = 20;
		}
	}
	public function getMyYaw($mx, $mz){
		if($mz == 0){
			if($mx < 0){
				$yaw = -90;
			}else{
				$yaw = 90;
			}
		}else{
			if($mx >= 0 and $mz > 0){
				$atan = atan($mx / $mz);
				$yaw = rad2deg($atan);
			}elseif($mx >= 0 and $mz < 0){
				$atan = atan($mx / abs($mz));
				$yaw = 180 - rad2deg($atan);
			}elseif($mx < 0 and $mz < 0){
				$atan = atan($mx / $mz);
				$yaw = -(180 - rad2deg($atan));
			}elseif($mx < 0 and $mz > 0){
				$atan = atan(abs($mx) / $mz);
				$yaw = -(rad2deg($atan));
			}else{
				$yaw = 0;
			}
		}

		$yaw = -$yaw;
		return $yaw;
	}
	public function getMyPitch(Vector3 $from, Vector3 $to){
		$distance = $from->distance($to);
		$height = $to->y - $from->y;
		if($height > 0){
			return -rad2deg(asin($height / $distance));
		}elseif($height < 0){
			return rad2deg(asin(-$height / $distance));
		}else{
			return 0;
		}
	}
}