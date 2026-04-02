<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\server;

use pocketmine\event\Cancellable;
class NetworkInterfaceRegisterEvent extends NetworkInterfaceEvent implements Cancellable{
	public static $handlerList = null;

}