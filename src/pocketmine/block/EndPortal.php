<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;

class EndPortal extends Solid implements SolidLight{

	protected $id = self::END_PORTAL;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getLightLevel(){
		return 1;
	}
	public function getName() : string{
		return "End Portal";
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