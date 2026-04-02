<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

class Hopper extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Block::HOPPER_BLOCK);
		parent::__construct(self::HOPPER, $meta, $count, "Hopper");
	}
}