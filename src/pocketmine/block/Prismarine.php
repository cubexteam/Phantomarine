<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;

class Prismarine extends Solid{

	const NORMAL_PRISMARINE = 0;
	const DARK_PRISMARINE = 1;
	const BRICKS_PRISMARINE = 2;

	protected $id = self::PRISMARINE;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 1.5;
	}
	public function getName() : string{
		static $names = [
			self::NORMAL_PRISMARINE => "Prismarine",
			self::DARK_PRISMARINE => "Dark Prismarine",
			self::BRICKS_PRISMARINE => "Prismarine Bricks",
		];
		return $names[$this->meta & 0x03] ?? "Unknown";
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= Tool::TIER_WOODEN){
			return [
				[$this->id, $this->meta & 0x03, 1],
			];
		}else{
			return [];
		}
	}
}