<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class ItemFrame extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::ITEM_FRAME_BLOCK);
		parent::__construct(self::ITEM_FRAME, $meta, $count, "Item Frame");
	}
}