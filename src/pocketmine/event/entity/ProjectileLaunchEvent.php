<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Projectile;
use pocketmine\event\Cancellable;

class ProjectileLaunchEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;
	public function __construct(Projectile $entity){
		$this->entity = $entity;

	}
	public function getEntity(){
		return $this->entity;
	}

}