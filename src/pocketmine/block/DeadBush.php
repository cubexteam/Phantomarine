<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class DeadBush extends Flowable{

	protected $id = self::DEAD_BUSH;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Dead Bush";
	}
	public function getBurnChance() : int{
		return 60;
	}
	public function getBurnAbility() : int{
		return 100;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === Block::SAND or $down->getId() === Block::PODZOL or
			$down->getId() === Block::HARDENED_CLAY or $down->getId() === Block::STAINED_CLAY
		){
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
	public function getDrops(Item $item) : array{
		if($item->isShears()){
			return [
				[Item::DEAD_BUSH, 0, 1],
			];
		}else{
			return [
				[Item::STICK, 0, mt_rand(0, 2)],
			];
		}

	}

}
