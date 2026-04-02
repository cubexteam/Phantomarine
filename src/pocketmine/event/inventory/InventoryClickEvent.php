<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class InventoryClickEvent extends InventoryEvent implements Cancellable{
	public static $handlerList = null;
	private $who;
	private $slot;
	private $item;
	public function __construct(Inventory $inventory, Player $who, int $slot, Item $item){
		$this->who = $who;
		$this->slot = $slot;
		$this->item = $item;
		parent::__construct($inventory);
	}
	public function getWhoClicked() : Player{
		return $this->who;
	}
	public function getPlayer() : Player{
		return $this->who;
	}
	public function getSlot() : int{
		return $this->slot;
	}
	public function getItem() : Item{
		return $this->item;
	}
}