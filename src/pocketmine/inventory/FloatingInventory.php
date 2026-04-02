<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;
class FloatingInventory extends BaseInventory{
	public function __construct(InventoryHolder $holder){
		parent::__construct($holder, InventoryType::get(InventoryType::PLAYER_FLOATING));
	}
}