<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class DoubleWoodSlab extends Solid{

	protected $id = self::DOUBLE_WOOD_SLAB;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 2;
	}
	public function getToolType(){
		return Tool::TYPE_AXE;
	}
	public function getBurnChance() : int{
		return 5;
	}
	public function getBurnAbility() : int{
		return 20;
	}
	public function getName() : string{
		static $names = [
			0 => "Oak",
			1 => "Spruce",
			2 => "Birch",
			3 => "Jungle",
			4 => "Acacia",
			5 => "Dark Oak"
		];
		return "Double " . ($names[$this->meta & 0x07] ?? "") . " Wooden Slab";
	}
	public function getDrops(Item $item) : array{
		return [
			[Item::WOOD_SLAB, $this->meta & 0x07, 2],
		];
	}

}