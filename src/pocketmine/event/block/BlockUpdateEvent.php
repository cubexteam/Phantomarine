<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\block;

use pocketmine\event\Cancellable;
class BlockUpdateEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;

}