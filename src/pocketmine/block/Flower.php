<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Flower extends Flowable{
	const TYPE_POPPY = 0;
	const TYPE_BLUE_ORCHID = 1;
	const TYPE_ALLIUM = 2;
	const TYPE_AZURE_BLUET = 3;
	const TYPE_RED_TULIP = 4;
	const TYPE_ORANGE_TULIP = 5;
	const TYPE_WHITE_TULIP = 6;
	const TYPE_PINK_TULIP = 7;
	const TYPE_OXEYE_DAISY = 8;

	protected $id = self::RED_FLOWER;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		static $names = [
			self::TYPE_POPPY => "Poppy",
			self::TYPE_BLUE_ORCHID => "Blue Orchid",
			self::TYPE_ALLIUM => "Allium",
			self::TYPE_AZURE_BLUET => "Azure Bluet",
			self::TYPE_RED_TULIP => "Red Tulip",
			self::TYPE_ORANGE_TULIP => "Orange Tulip",
			self::TYPE_WHITE_TULIP => "White Tulip",
			self::TYPE_PINK_TULIP => "Pink Tulip",
			self::TYPE_OXEYE_DAISY => "Oxeye Daisy"
		];
		return $names[$this->meta] ?? "Unknown";
	}
	public function getBurnChance() : int{
		return 60;
	}
	public function getBurnAbility() : int{
		return 100;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === Block::GRASS or $down->getId() === Block::DIRT or $down->getId() === Block::FARMLAND){
			$this->getLevel()->setBlock($block, $this, true);

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			$this->getLevel()->useBreakOn($this);
		}
	}
}