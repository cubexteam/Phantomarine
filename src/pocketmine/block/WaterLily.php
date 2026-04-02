<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class WaterLily extends Flowable{

	protected $id = self::WATER_LILY;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Lily Pad";
	}
	public function getHardness(){
		return 0.6;
	}
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x + 0.0625,
			$this->y,
			$this->z + 0.0625,
			$this->x + 0.9375,
			$this->y + 0.015625,
			$this->z + 0.9375
		);
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target instanceof Water){
			$up = $target->getSide(Vector3::SIDE_UP);
			if($up->getId() === Block::AIR){
				$this->getLevel()->setBlock($up, $this, true, true);
				return true;
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!($this->getSide(Vector3::SIDE_DOWN) instanceof Water)){
			$this->getLevel()->useBreakOn($this);
		}
	}
	public function getDrops(Item $item) : array{
		return [
			[$this->id, 0, 1]
		];
	}
}