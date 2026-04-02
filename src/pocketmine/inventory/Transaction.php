<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Player;

interface Transaction{

	const TYPE_NORMAL = 0;
	const TYPE_DROP_ITEM = 1;
	public function getInventory();
	public function getSlot();
	public function getTargetItem();
	public function getSourceItem();
	public function getCreationTime();
	public function execute(Player $source) : bool;
}
