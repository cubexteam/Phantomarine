<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

class EyeOfEnder extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::EYE_OF_ENDER, $meta, $count, "Eye Of Ender");
	}

}