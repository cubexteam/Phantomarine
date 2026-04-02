<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class WoodenDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::WOODEN_DOOR_BLOCK);
		parent::__construct(self::WOODEN_DOOR, $meta, $count, "Wooden Door");
	}
}