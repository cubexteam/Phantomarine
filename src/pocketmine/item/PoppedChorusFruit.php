<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;


class PoppedChorusFruit extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::POPPED_CHORUS_FRUIT, $meta, $count, "Popped Chorus Fruit");
	}

}