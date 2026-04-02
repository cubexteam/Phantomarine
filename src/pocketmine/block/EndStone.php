<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


use pocketmine\item\Tool;

class EndStone extends Solid{

	protected $id = self::END_STONE;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "End Stone";
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function getHardness(){
		return 3;
	}
}