<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class Farmland extends Transparent{

	protected $id = self::FARMLAND;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Farmland";
	}
	public function getHardness(){
		return 0.6;
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1,
			$this->z + 1
		);
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_UP)->isSolid()){
			$this->level->setBlock($this, BlockFactory::get(BlockIds::DIRT), true);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if (!$this->canHydrate()) {
			if ($this->meta > 0) {
				$this->meta--;
				$this->level->setBlock($this, $this, false, false);
			} else {
				$this->level->setBlock($this, BlockFactory::get(Block::DIRT), false, true);
			}
		} elseif ($this->meta < 7) {
			$this->meta = 7;
			$this->level->setBlock($this, $this, false, false);
		}
	}

	protected function canHydrate() : bool{
		$start = $this->add(-4, 0, -4);
		$end = $this->add(4, 1, 4);
		for($y = $start->y; $y <= $end->y; ++$y){
			for($z = $start->z; $z <= $end->z; ++$z){
				for($x = $start->x; $x <= $end->x; ++$x){
					$id = $this->level->getBlockIdAt($x, $y, $z);
					if($id === Block::STILL_WATER or $id === Block::WATER){
						return true;
					}
				}
			}
		}
		return false;
	}
	public function getDrops(Item $item) : array{
		return [
			[Item::DIRT, 0, 1],
		];
	}
}