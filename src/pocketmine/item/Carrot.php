<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class Carrot extends Food{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::CARROT_BLOCK);
		parent::__construct(self::CARROT, $meta, $count, "Carrot");
	}
	public function getFoodRestore() : int{
		return 3;
	}
	public function getSaturationRestore() : float{
		return 4.8;
	}
}
