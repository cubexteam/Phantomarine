<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\event\Event;

abstract class EntityEvent extends Event{
	protected $entity;
	public function getEntity(){
		return $this->entity;
	}
}