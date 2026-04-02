<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

class RedstoneLamp extends Solid{
	protected $id = self::REDSTONE_LAMP;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Redstone Lamp";
	}
	public function turnOn(){
		$this->getLevel()->setBlock($this, new LitRedstoneLamp(), true, true);
		return true;
	}

	public function getHardness(){
		return 0.3;
	}
}
