<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

class StillWater extends Water{

	protected $id = self::STILL_WATER;
	public function getName() : string{
		return "Still Water";
	}
}