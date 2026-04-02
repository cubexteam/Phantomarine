<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\inventory\Recipe;
use pocketmine\item\Item;
use pocketmine\Player;

class CraftItemEvent extends Event implements Cancellable{
	public static $handlerList = null;
	private $input = [];
	private $recipe;
	private $player;
	public function __construct(Player $player, array $input, Recipe $recipe){
		$this->player = $player;
		$this->input = $input;
		$this->recipe = $recipe;
	}
	public function getInput(){
		$items = [];
		foreach($this->input as $i => $item){
			$items[$i] = clone $item;
		}
		return $items;
	}
	public function getRecipe(){
		return $this->recipe;
	}
	public function getPlayer(){
		return $this->player;
	}
}