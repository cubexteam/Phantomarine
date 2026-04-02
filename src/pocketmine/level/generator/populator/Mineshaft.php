<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator\populator;

use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;

class Mineshaft extends Populator{
	private static $ODD = 3;
	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		if($random->nextRange(0, self::$ODD) === 0){
		}
	}

}