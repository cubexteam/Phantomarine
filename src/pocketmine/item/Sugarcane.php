<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class Sugarcane extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::SUGARCANE_BLOCK);
		parent::__construct(self::SUGARCANE, $meta, $count, "Sugar Cane");
	}
}