<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\inventory;

use pocketmine\event\Event;
use pocketmine\inventory\Inventory;

abstract class InventoryEvent extends Event{
	protected $inventory;
	public function __construct(Inventory $inventory){
		$this->inventory = $inventory;
	}
	public function getInventory(){
		return $this->inventory;
	}
	public function getViewers(){
		return $this->inventory->getViewers();
	}
}