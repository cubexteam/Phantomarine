<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\inventory\Inventory;
use pocketmine\Player;

class InventoryOpenEvent extends InventoryEvent implements Cancellable{
	public static $handlerList = null;
	private $who;
	public function __construct(Inventory $inventory, Player $who){
		$this->who = $who;
		parent::__construct($inventory);
	}
	public function getPlayer(){
		return $this->who;
	}

}