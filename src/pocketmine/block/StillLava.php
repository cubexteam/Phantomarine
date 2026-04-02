<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

class StillLava extends Lava{

	protected $id = self::STILL_LAVA;
	public function getName() : string{
		return "Still Lava";
	}

}