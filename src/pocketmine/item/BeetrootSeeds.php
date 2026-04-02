<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class BeetrootSeeds extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::BEETROOT_BLOCK);
		parent::__construct(self::BEETROOT_SEEDS, $meta, $count, "Beetroot Seeds");
	}
}