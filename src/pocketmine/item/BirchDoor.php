<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class BirchDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::BIRCH_DOOR_BLOCK);
		parent::__construct(self::BIRCH_DOOR, $meta, $count, "Birch Door");
	}
}