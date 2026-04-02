<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\Player;
class PlayerJumpEvent extends PlayerEvent{
	public static $handlerList = null;
	public function __construct(Player $player){
		$this->player = $player;
	}
}