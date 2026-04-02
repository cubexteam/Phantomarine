<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item\enchantment;


class EnchantmentList{
	private $enchantments;
	public function __construct($size){
		$this->enchantments = new \SplFixedArray($size);
	}
	public function setSlot($slot, EnchantmentEntry $entry){
		$this->enchantments[$slot] = $entry;
	}
	public function getSlot($slot){
		return $this->enchantments[$slot];
	}
	public function getSize(){
		return $this->enchantments->getSize();
	}

}