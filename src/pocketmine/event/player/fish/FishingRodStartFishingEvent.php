<?php

declare(strict_types=1);

namespace pocketmine\event\player\fish;

use pocketmine\entity\FishingHook;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class FishingRodStartFishingEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	protected $hook;
	protected $force;
	protected $f1;
	protected $f2;

	public function __construct(Player $fisher, FishingHook $hook, float $force, float $f1, float $f2){
		$this->player = $fisher;
		$this->hook = $hook;
		$this->force = $force;
		$this->f1 = $f1;
		$this->f2 = $f2;
	}
	public function getHook() : FishingHook{
		return $this->hook;
	}
	public function getForce() : float{
		return $this->force;
	}
	public function setForce(float $force) : void{
		$this->force = $force;
	}
	public function getF1() : float{
		return $this->f1;
	}
	public function setF1(float $f1) : void{
		$this->f1 = $f1;
	}
	public function getF2() : float{
		return $this->f2;
	}
	public function setF2(float $f2) : void{
		$this->f2 = $f2;
	}
}