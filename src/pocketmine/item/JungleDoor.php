<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class JungleDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::JUNGLE_DOOR_BLOCK);
		parent::__construct(self::JUNGLE_DOOR, $meta, $count, "Jungle Door");
	}
}