<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\block\utils\ColorBlockMetaHelper;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Carpet extends Flowable{

	protected $id = self::CARPET;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 0.1;
	}
	public function isSolid(){
		return true;
	}
	public function getBurnChance() : int{
		return 30;
	}
	public function getBurnAbility() : int{
		return 20;
	}
	public function getName() : string{
		return ColorBlockMetaHelper::getColorFromMeta($this->meta) . " Carpet";
	}
	protected function recalculateBoundingBox(){

		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 0.0625,
			$this->z + 1
		);
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() !== self::AIR){
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}elseif($down->getId() == self::CARPET){
			return false;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
			$this->getLevel()->useBreakOn($this);
		}
	}
}