<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class NetherWart extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::NETHER_WART_BLOCK);
		parent::__construct(self::NETHER_WART, $meta, $count, "Nether Wart");
	}
}
