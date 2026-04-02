<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerToggleGlideEvent extends PlayerEvent implements Cancellable{

	public static $handlerList = null;
	protected $isGliding;
	public function __construct(Player $player, $isGliding){
		$this->player = $player;
		$this->isGliding = (bool) $isGliding;
	}
	public function isGliding(){
		return $this->isGliding;
	}
	public function getName(){
		return "PlayerToggleGlideEvent";
	}

}
