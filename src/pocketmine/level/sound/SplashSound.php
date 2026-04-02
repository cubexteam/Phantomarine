<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class SplashSound extends GenericSound{
	public function __construct(Vector3 $pos, $pitch = 0){
		parent::__construct($pos, LevelEventPacket::EVENT_CAULDRON_FILL_WATER, $pitch);
	}
}