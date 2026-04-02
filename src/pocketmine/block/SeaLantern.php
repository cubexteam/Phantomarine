<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;

class SeaLantern extends Transparent{

	protected $id = self::SEA_LANTERN;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Sea Lantern";
	}
	public function getHardness(){
		return 0.3;
	}
	public function getLightLevel(){
		return 15;
	}
	public function getDrops(Item $item) : array{
		if($item->hasEnchantment(Enchantment::TYPE_MINING_SILK_TOUCH)){
			return [
				[$this->id, 0, 1],
			];
		}
		return [
			[Item::PRISMARINE_CRYSTALS, 0, 3],
		];
	}

}