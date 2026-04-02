<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


abstract class Transparent extends Block{
	public function isTransparent(){
		return true;
	}

	public function getLightFilter() : int{
		return 0;
	}
}