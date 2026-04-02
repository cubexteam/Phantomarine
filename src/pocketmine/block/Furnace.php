<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


class Furnace extends BurningFurnace{

	protected $id = self::FURNACE;
	public function getName() : string{
		return "Furnace";
	}

	public function getLightLevel(){
		return 0;
	}
}