<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;

class EntityEffectAddEvent extends EntityEffectEvent{
	public static $handlerList = null;
	private $modify;
	private $oldEffect;

	public function __construct(Entity $entity, Effect $effect, $modify, $oldEffect){
		parent::__construct($entity, $effect);
		$this->modify = $modify;
		$this->oldEffect = $oldEffect;
	}

	public function willModify() : bool{
		return $this->modify;
	}

	public function setWillModify(bool $modify){
		$this->modify = $modify;
	}

	public function hasOldEffect() : bool{
		return $this->oldEffect instanceof Effect;
	}
	public function getOldEffect(){
		return $this->oldEffect;
	}
}