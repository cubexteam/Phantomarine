<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
class EntityDamageByEntityEvent extends EntityDamageEvent{
	private $damagerEid;
	private $knockBack;
	public function __construct(Entity $damager, Entity $entity, int $cause, $damage, float $knockBack = 0.4){
		$this->damagerEid = $damager->getId();
		$this->knockBack = $knockBack;
		parent::__construct($entity, $cause, $damage);
		$this->addAttackerModifiers($damager);
	}
	protected function addAttackerModifiers(Entity $damager){
		if($damager->hasEffect(Effect::STRENGTH)){
			$this->setRateDamage(1 + 0.3 * ($damager->getEffect(Effect::STRENGTH)->getEffectLevel()), self::MODIFIER_STRENGTH);
		}

		if($damager->hasEffect(Effect::WEAKNESS)){
			$eff_level = 1 - 0.2 * ($damager->getEffect(Effect::WEAKNESS)->getEffectLevel());
			if($eff_level < 0){
				$eff_level = 0;
			}
			$this->setRateDamage($eff_level, self::MODIFIER_WEAKNESS);
		}
	}
	public function getDamager(){
		return $this->getEntity()->getLevel()->getServer()->findEntity($this->damagerEid);
	}
	public function getKnockBack() : float{
		return $this->knockBack;
	}
	public function setKnockBack(float $knockBack){
		$this->knockBack = $knockBack;
	}
}
