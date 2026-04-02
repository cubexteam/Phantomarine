<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\event\Event;

abstract class PlayerEvent extends Event{
	protected $player;
	public function getPlayer(){
		return $this->player;
	}
}