<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;


use pocketmine\event\Cancellable;
use pocketmine\level\Position;

class EntityGenerateEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;

	const CAUSE_AI_HOLDER = 0;
	const CAUSE_MOB_SPAWNER = 1;
	private $position;
	private $cause;
	private $entityType;
	public function __construct(Position $pos, int $entityType, int $cause = self::CAUSE_MOB_SPAWNER){
		$this->position = $pos;
		$this->entityType = $entityType;
		$this->cause = $cause;
	}
	public function getPosition(){
		return $this->position;
	}
	public function setPosition(Position $pos){
		$this->position = $pos;
	}
	public function getType() : int{
		return $this->entityType;
	}
	public function getCause() : int{
		return $this->cause;
	}
}