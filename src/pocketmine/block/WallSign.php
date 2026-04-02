<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

class WallSign extends SignPost{

	protected $id = self::WALL_SIGN;
	public function getName() : string{
		return "Wall Sign";
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide($this->meta ^ 0x01)->getId() === self::AIR){
			$this->getLevel()->useBreakOn($this);
		}
	}
}