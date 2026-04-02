<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class ButtonClickSound extends GenericSound{
	public function __construct(Vector3 $pos){
		parent::__construct($pos, LevelEventPacket::EVENT_REDSTONE_TRIGGER);
	}
}