<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\tile;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

interface Container{
	public function getItem($index);
	public function setItem($index, Item $item);
	public function getSize();
	public function getInventory();
}
