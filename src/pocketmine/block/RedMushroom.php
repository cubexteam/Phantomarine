<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class RedMushroom extends Flowable{

	protected $id = self::RED_MUSHROOM;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Red Mushroom";
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			$this->getLevel()->useBreakOn($this);
		}
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->isTransparent() === false){
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}
}