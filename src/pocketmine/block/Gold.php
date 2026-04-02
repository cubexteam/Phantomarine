<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class Gold extends Solid{

	protected $id = self::GOLD_BLOCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Gold Block";
	}
	public function getHardness(){
		return 3;
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= 4){
			return [
				[Item::GOLD_BLOCK, 0, 1],
			];
		}else{
			return [];
		}
	}
}