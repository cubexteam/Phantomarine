<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\level\Position;
use pocketmine\Player;
class PlayerRespawnEvent extends PlayerEvent{
	public static $handlerList = null;
	protected $position;

	public function __construct(Player $player, Position $position){
		$this->player = $player;
		$this->position = $position;
	}

	public function getRespawnPosition() : Position{
		return $this->position;
	}

	public function setRespawnPosition(Position $position) : void{
		if(!$position->isValid()){
			throw new \InvalidArgumentException("Spawn position must reference a valid and loaded World");
		}
		$this->position = $position;
	}
}