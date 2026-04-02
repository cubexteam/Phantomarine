<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\Player;
class PlayerQuitEvent extends PlayerEvent{
	public static $handlerList = null;
	protected $quitMessage;
	protected $quitReason;
	protected $autoSave = true;
	public function __construct(Player $player, $quitMessage, string $quitReason, $autoSave = true){
		$this->player = $player;
		$this->quitMessage = $quitMessage;
		$this->quitReason = $quitReason;
		$this->autoSave = $autoSave;
	}
	public function setQuitMessage($quitMessage){
		$this->quitMessage = $quitMessage;
	}
	public function getQuitMessage(){
		return $this->quitMessage;
	}
	public function getQuitReason() : string{
		return $this->quitReason;
	}
	public function getAutoSave(){
		return $this->autoSave;
	}
	public function setAutoSave($value = true){
		$this->autoSave = (bool) $value;
	}
}