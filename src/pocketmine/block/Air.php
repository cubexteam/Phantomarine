<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
class Air extends Transparent{

	protected $id = self::AIR;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Air";
	}
	public function isBreakable(Item $item){
		return false;
	}
	public function canBeFlowedInto(){
		return true;
	}
	public function canBeReplaced(){
		return true;
	}
	public function canBePlaced(){
		return false;
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}
	public function isSolid(){
		return false;
	}
	public function getBoundingBox(){
		return null;
	}

	public function getCollisionBoxes() : array{
		return [];
	}
	public function getHardness(){
		return -1;
	}
	public function getResistance(){
		return 0;
	}

}