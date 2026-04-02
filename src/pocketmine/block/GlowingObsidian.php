<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


class GlowingObsidian extends Solid implements SolidLight{

	protected $id = self::GLOWING_OBSIDIAN;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Glowing Obsidian";
	}
	public function getLightLevel(){
		return 12;
	}

	public function getHardness() : float{
		return 10;
	}

	public function getResistance(){
		return 50;
	}
}