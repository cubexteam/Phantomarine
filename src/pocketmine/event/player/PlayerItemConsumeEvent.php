<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;
class PlayerItemConsumeEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	private $item;
	public function __construct(Player $player, Item $item){
		$this->player = $player;
		$this->item = $item;
	}
	public function getItem(){
		return clone $this->item;
	}

}