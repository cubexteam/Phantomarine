<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\block\utils\Fallable;
use pocketmine\block\utils\FallableTrait;
use pocketmine\item\Item;

class DragonEgg extends Solid implements Fallable{
	use FallableTrait;

	protected $id = self::DRAGON_EGG;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName(){
		return "Dragon Egg";
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
