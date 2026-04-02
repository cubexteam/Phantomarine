<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\Player;
class SignChangeEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;
	private $player;
	private $lines = [];
	public function __construct(Block $theBlock, Player $thePlayer, array $theLines){
		parent::__construct($theBlock);
		$this->player = $thePlayer;
		$this->lines = $theLines;
	}
	public function getPlayer(){
		return $this->player;
	}
	public function getLines(){
		return $this->lines;
	}
	public function getLine($index){
		if($index < 0 or $index > 3){
			throw new \InvalidArgumentException("Index must be in the range 0-3!");
		}
		return $this->lines[$index];
	}
	public function setLines(array $lines){
		if(count($lines) !== 4){
			throw new \InvalidArgumentException("Array size must be 4!");
		}
		$this->lines = $lines;
	}
	public function setLine($index, $line){
		if($index < 0 or $index > 3){
			throw new \InvalidArgumentException("Index must be in the range 0-3!");
		}
		$this->lines[$index] = $line;
	}
}