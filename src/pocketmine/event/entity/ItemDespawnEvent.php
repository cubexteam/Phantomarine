<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\entity;

use pocketmine\entity\Item;
use pocketmine\event\Cancellable;

class ItemDespawnEvent extends EntityEvent implements Cancellable{
	public static $handlerList = null;
	public function __construct(Item $item){
		$this->entity = $item;

	}
	public function getEntity(){
		return $this->entity;
	}

}