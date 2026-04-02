<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

abstract class Flowable extends Transparent{
	public function canBeFlowedInto(){
		return true;
	}
	public function getHardness(){
		return 0;
	}
	public function isSolid(){
		return false;
	}

	protected function recalculateBoundingBox(){
		return null;
	}
}