<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

class RabbitStew extends Food{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::RABBIT_STEW, $meta, $count, "Rabbit Stew");
	}
	public function getMaxStackSize() : int{
		return 1;
	}
	public function getFoodRestore() : int{
		return 10;
	}
	public function getSaturationRestore() : float{
		return 12;
	}
	public function getResidue(){
		return Item::get(Item::BOWL);
	}
}
