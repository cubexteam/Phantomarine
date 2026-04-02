<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class WheatSeeds extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::WHEAT_BLOCK);
		parent::__construct(self::WHEAT_SEEDS, $meta, $count, "Wheat Seeds");
	}
}