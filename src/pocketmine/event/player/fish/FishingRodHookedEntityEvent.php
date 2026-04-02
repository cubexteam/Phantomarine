<?php

declare(strict_types=1);

namespace pocketmine\event\player\fish;

use pocketmine\entity\Entity;
use pocketmine\entity\FishingHook;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class FishingRodHookedEntityEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	protected $hook;
	protected $hookedEntity;
	protected $force;

	public function __construct(Player $fisher, FishingHook $hook, Entity $hookedEntity){
		$this->player = $fisher;
		$this->hook = $hook;
		$this->hookedEntity = $hookedEntity;
	}
	public function getHookedEntity() : Entity{
		return $this->hookedEntity;
	}
	public function getHook() : FishingHook{
		return $this->hook;
	}
}