<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


use pocketmine\block\utils\Fallable;
use pocketmine\block\utils\FallableTrait;
use pocketmine\item\Tool;

class Sand extends Solid implements Fallable{
	use FallableTrait;

	const NORMAL = 0;
	const RED = 1;

	protected $id = self::SAND;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 0.5;
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}
	public function getName() : string{
		if($this->meta === 0x01){
			return "Red Sand";
		}

		return "Sand";
	}

}