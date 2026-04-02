<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;

class Bedrock extends Solid{

	protected $id = self::BEDROCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Bedrock";
	}
	public function getHardness(){
		return -1;
	}
	public function getResistance(){
		return 18000000;
	}
	public function isBreakable(Item $item){
		return false;
	}

}
