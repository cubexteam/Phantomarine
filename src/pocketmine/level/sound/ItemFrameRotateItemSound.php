<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class ItemFrameRotateItemSound extends GenericSound{
	public function __construct(Vector3 $pos, $pitch = 0){
		parent::__construct($pos, LevelEventPacket::EVENT_SOUND_ITEMFRAME_ROTATE_ITEM, $pitch);
	}
}
