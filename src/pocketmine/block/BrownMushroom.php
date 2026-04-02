<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

class BrownMushroom extends RedMushroom{

	protected $id = self::BROWN_MUSHROOM;
	public function getName() : string{
		return "Brown Mushroom";
	}
	public function getLightLevel(){
		return 1;
	}

	protected function recalculateBoundingBox(){
		return null;
	}
}