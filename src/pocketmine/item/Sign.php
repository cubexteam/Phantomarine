<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class Sign extends Item{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::SIGN_POST);
		parent::__construct(self::SIGN, $meta, $count, "Sign");
	}
	public function getMaxStackSize() : int{
		return 16;
	}
}