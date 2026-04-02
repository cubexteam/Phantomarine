<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class Potato extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::POTATO_BLOCK);
		parent::__construct(self::POTATO, $meta, $count, "Potato");
	}
}