<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;


class Shears extends Tool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::SHEARS, $meta, $count, "Shears");
	}

	public function getMaxDurability() : int{
		return 239;
	}

	public function isShears(){
		return true;
	}
}