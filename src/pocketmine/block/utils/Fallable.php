<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\Block;

interface Fallable{
	public function tickFalling() : ?Block;
}