<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class GoldOre extends Solid{

	protected $id = self::GOLD_ORE;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Gold Ore";
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
				[Item::GOLD_ORE, 0, 1],
			];
		}else{
			return [];
		}
	}
}