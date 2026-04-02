<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\TextContainer;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerDeathEvent extends EntityDeathEvent{
	public static $handlerList = null;
	protected $player;
	private $deathMessage;
	private $keepInventory = false;
	private $keepExperience = false;
	public function __construct(Player $entity, array $drops, $deathMessage){
		parent::__construct($entity, $drops);
		$this->player = $entity;
		$this->deathMessage = $deathMessage;
	}
	public function getEntity(){
		return $this->player;
	}
	public function getPlayer(){
		return $this->player;
	}
	public function getDeathMessage(){
		return $this->deathMessage;
	}
	public function setDeathMessage($deathMessage){
		$this->deathMessage = $deathMessage;
	}
	public function getKeepInventory() : bool{
		return $this->keepInventory;
	}
	public function setKeepInventory(bool $keepInventory){
		$this->keepInventory = $keepInventory;
	}
	public function getKeepExperience() : bool{
		return $this->keepExperience;
	}
	public function setKeepExperience(bool $keepExperience){
		$this->keepExperience = $keepExperience;
	}
}