<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;


abstract class Animal extends Creature implements Ageable{
	public function isBaby(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BABY);
	}
}