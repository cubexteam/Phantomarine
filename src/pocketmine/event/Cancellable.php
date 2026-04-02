<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;
interface Cancellable{
	public function isCancelled();
	public function setCancelled($forceCancel = false);
}