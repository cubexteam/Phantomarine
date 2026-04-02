<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class IronDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::IRON_DOOR_BLOCK);
		parent::__construct(self::IRON_DOOR, $meta, $count, "Iron Door");
	}
}