<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class FlowerPot extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::FLOWER_POT_BLOCK);
		parent::__construct(self::FLOWER_POT, $meta, $count, "Flower Pot");
	}
	public function getMaxStackSize() : int{
		return 64;
	}
} 
