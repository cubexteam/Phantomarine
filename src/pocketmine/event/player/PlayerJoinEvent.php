<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\event\TextContainer;
use pocketmine\Player;
class PlayerJoinEvent extends PlayerEvent{
	public static $handlerList = null;
	protected $joinMessage;
	public function __construct(Player $player, $joinMessage){
		$this->player = $player;
		$this->joinMessage = $joinMessage;
	}
	public function setJoinMessage($joinMessage){
		$this->joinMessage = $joinMessage;
	}
	public function getJoinMessage(){
		return $this->joinMessage;
	}

}