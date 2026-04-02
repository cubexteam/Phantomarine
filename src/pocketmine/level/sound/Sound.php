<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\DataPacket;

abstract class Sound extends Vector3{
	abstract public function encode();

}
