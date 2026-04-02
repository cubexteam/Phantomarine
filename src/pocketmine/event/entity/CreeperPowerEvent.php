<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Creeper;
use pocketmine\entity\Lightning;
use pocketmine\event\Cancellable;

class CreeperPowerEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	const CAUSE_SET_ON = 0;
	const CAUSE_SET_OFF = 1;
	const CAUSE_LIGHTNING = 2;
	private $lightning;

	private $cause;
	public function __construct(Creeper $creeper, Lightning $lightning = null, int $cause = self::CAUSE_LIGHTNING){
		$this->entity = $creeper;
		$this->lightning = $lightning;
		$this->cause = $cause;
	}
	public function getLightning(){
		return $this->lightning;
	}
	public function getCause(){
		return $this->cause;
	}
}
