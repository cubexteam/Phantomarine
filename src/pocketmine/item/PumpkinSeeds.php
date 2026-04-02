<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class PumpkinSeeds extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::PUMPKIN_STEM);
		parent::__construct(self::PUMPKIN_SEEDS, $meta, $count, "Pumpkin Seeds");
	}
}