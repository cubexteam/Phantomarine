<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class Cake extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::CAKE_BLOCK);
		parent::__construct(self::CAKE, $meta, $count, "Cake");
	}
	public function getMaxStackSize() : int{
		return 1;
	}
}