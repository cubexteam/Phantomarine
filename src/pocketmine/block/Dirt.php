<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Player;

class Dirt extends Solid{

	protected $id = self::DIRT;
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
		return "Dirt";
	}
	public function onActivate(Item $item, Player $player = null){
		if($item->isHoe()){
			$item->useOn($this, 2);
			$this->getLevel()->setBlock($this, BlockFactory::get(Item::FARMLAND, 0), true);

			return true;
		}

		return false;
	}
}