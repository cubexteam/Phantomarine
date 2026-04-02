<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class SpruceDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::SPRUCE_DOOR_BLOCK);
		parent::__construct(self::SPRUCE_DOOR, $meta, $count, "Spruce Door");
	}
}