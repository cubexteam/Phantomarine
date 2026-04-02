<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class DarkOakDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::DARK_OAK_DOOR_BLOCK);
		parent::__construct(self::DARK_OAK_DOOR, $meta, $count, "Dark Oak Door");
	}
}