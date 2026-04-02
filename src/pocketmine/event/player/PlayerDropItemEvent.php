<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;
class PlayerDropItemEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	private $drop;
	public function __construct(Player $player, Item $drop){
		$this->player = $player;
		$this->drop = $drop;
	}
	public function getItem(){
		return $this->drop;
	}

}