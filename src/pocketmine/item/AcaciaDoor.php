<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\BlockFactory;

class AcaciaDoor extends Door{
	public function __construct($meta = 0, $count = 1){
		$this->block = BlockFactory::get(Item::ACACIA_DOOR_BLOCK);
		parent::__construct(self::ACACIA_DOOR, $meta, $count, "Acacia Door");
	}
}