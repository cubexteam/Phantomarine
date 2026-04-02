<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\math\Vector3;

class EntityMotionEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;
	private $mot;
	public function __construct(Entity $entity, Vector3 $mot){
		$this->entity = $entity;
		$this->mot = $mot;
	}
	public function getVector(){
		return $this->mot;
	}


}
