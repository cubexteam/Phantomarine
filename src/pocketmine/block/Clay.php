<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class Clay extends Solid{

	protected $id = self::CLAY_BLOCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 0.6;
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}
	public function getName() : string{
		return "Clay Block";
	}
	public function getDrops(Item $item) : array{
		return [
			[Item::CLAY, 0, 4],
		];
	}
}