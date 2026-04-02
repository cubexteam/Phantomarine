<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;
class PlayerAnimationEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	const ARM_SWING = 1;
	const WAKE_UP = 3;
	private $animationType;
	public function __construct(Player $player, int $animation){
		$this->player = $player;
		$this->animationType = $animation;
	}
	public function getAnimationType() : int{
		return $this->animationType;
	}

}
