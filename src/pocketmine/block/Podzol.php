<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;

class Podzol extends Solid{

	protected $id = self::PODZOL;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}
	public function getName() : string{
		return "Podzol";
	}
	public function getHardness(){
		return 0.5;
	}
	public function getResistance(){
		return 2.5;
	}
	public function getDrops(Item $item) : array{
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::PODZOL, 0, 1],
			];
		}else{
			return [
				[Item::DIRT, 0, 1],
			];
		}

	}
}
