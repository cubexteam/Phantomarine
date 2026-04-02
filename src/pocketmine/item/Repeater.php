<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

class Repeater extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Block::UNPOWERED_REPEATER_BLOCK);
		parent::__construct(self::REPEATER, $meta, $count, "Repeater");
	}
}