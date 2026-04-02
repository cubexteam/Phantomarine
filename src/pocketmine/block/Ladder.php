<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

class Ladder extends Transparent{

	protected $id = self::LADDER;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Ladder";
	}
	public function hasEntityCollision(){
		return true;
	}

	public function canClimb() : bool{
		return true;
	}
	public function isSolid(){
		return false;
	}
	public function getHardness(){
		return 0.4;
	}
	public function onEntityInside(Entity $entity) : bool{
		if($entity instanceof Living and $entity->asVector3()->floor()->distanceSquared($this) < 1){
			$entity->resetFallDistance();
			$entity->onGround = true;
		}
		return true;
	}
	protected function recalculateBoundingBox(){
		$f = 0.1875;

		$minX = $minZ = 0;
		$maxX = $maxZ = 1;

		if($this->meta === 2){
			$minZ = 1 - $f;
		}elseif($this->meta === 3){
			$maxZ = $f;
		}elseif($this->meta === 4){
			$minX = 1 - $f;
		}elseif($this->meta === 5){
			$maxX = $f;
		}

		return new AxisAlignedBB(
			$this->x + $minX,
			$this->y,
			$this->z + $minZ,
			$this->x + $maxX,
			$this->y + 1,
			$this->z + $maxZ
		);
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$faces = [
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5,
			];
			if(isset($faces[$face])){
				$this->meta = $faces[$face];
				$this->getLevel()->setBlock($block, $this, true, true);

				return true;
			}
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if (!$this->getSide($this->meta ^ 0x01)->isSolid()) {
			$this->level->useBreakOn($this);
		}
	}
	public function getToolType(){
		return Tool::TYPE_AXE;
	}
	public function getDrops(Item $item) : array{
		return [
			[$this->id, 0, 1],
		];
	}
}
