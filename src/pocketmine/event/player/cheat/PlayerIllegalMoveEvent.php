<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player\cheat;

use pocketmine\event\Cancellable;
use pocketmine\math\Vector3;
use pocketmine\Player;

class PlayerIllegalMoveEvent extends PlayerCheatEvent implements Cancellable{
	public static $handlerList = null;
	private $attemptedPosition;
	private $originalPosition;
	private $expectedPosition;
	public function __construct(Player $player, Vector3 $attemptedPosition, Vector3 $originalPosition){
		$this->player = $player;
		$this->attemptedPosition = $attemptedPosition;
		$this->originalPosition = $originalPosition;
		$this->expectedPosition = $player->asVector3();
	}
	public function getAttemptedPosition() : Vector3{
		return $this->attemptedPosition;
	}
	public function getOriginalPosition() : Vector3{
		return $this->originalPosition;
	}
	public function getExpectedPosition() : Vector3{
		return $this->expectedPosition;
	}
}