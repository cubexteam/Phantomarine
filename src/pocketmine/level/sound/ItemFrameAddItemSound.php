<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class ItemFrameAddItemSound extends GenericSound{
	public function __construct(Vector3 $pos, $pitch = 0){
		parent::__construct($pos, LevelEventPacket::EVENT_SOUND_ITEMFRAME_ADD_ITEM, $pitch);
	}
}
