<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\plugin;

use pocketmine\event\Event;
use pocketmine\event\Listener;

interface EventExecutor{
	public function execute(Listener $listener, Event $event);
}