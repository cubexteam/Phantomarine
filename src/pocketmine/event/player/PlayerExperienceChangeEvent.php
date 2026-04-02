<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\entity\Human;
use pocketmine\event\Cancellable;

class PlayerExperienceChangeEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	public $progress;
	public $expLevel;
	public function __construct(Human $player, int $expLevel, float $progress){
		$this->progress = $progress;
		$this->expLevel = $expLevel;
		$this->player = $player;
	}
	public function getExpLevel(){
		return $this->expLevel;
	}
	public function setExpLevel($level){
		$this->expLevel = $level;
	}
	public function getProgress() : float{
		return $this->progress;
	}
	public function setProgress(float $progress){
		$this->progress = $progress;
	}
	public function getExp(){
		return Human::getLevelXpRequirement($this->expLevel) + $this->progress;
	}
	public function setExp($exp){
		$this->progress = $exp / Human::getLevelXpRequirement($this->expLevel);
	}
}
