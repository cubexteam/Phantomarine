<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;

class TextContainer{
	protected $text;
	public function __construct($text){
		$this->text = $text;
	}
	public function setText($text){
		$this->text = $text;
	}
	public function getText(){
		return $this->text;
	}
	public function __toString(){
		return $this->getText();
	}
}