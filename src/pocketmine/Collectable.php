<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine;

abstract class Collectable extends \Threaded{
	private $isGarbage = false;

	public function isGarbage() : bool{
		return $this->isGarbage;
	}
	public function setGarbage(){
		$this->isGarbage = true;
	}
}