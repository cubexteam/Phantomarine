<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class StoneWall extends Transparent{
	const NONE_MOSSY_WALL = 0;
	const MOSSY_WALL = 1;

	protected $id = self::STONE_WALL;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	public function getHardness(){
		return 2;
	}

	public function getName() : string{
		if($this->meta === 0x01){
			return "Mossy Cobblestone Wall";
		}

		return "Cobblestone Wall";
	}

	protected function recalculateBoundingBox(){

		$north = $this->canConnect($this->getSide(Vector3::SIDE_NORTH));
		$south = $this->canConnect($this->getSide(Vector3::SIDE_SOUTH));
		$west = $this->canConnect($this->getSide(Vector3::SIDE_WEST));
		$east = $this->canConnect($this->getSide(Vector3::SIDE_EAST));

		$inset = 0.25;
		if(
			$this->getSide(Vector3::SIDE_UP)->getId() === Block::AIR and
			(
				($north and $south and !$west and !$east) or
				(!$north and !$south and $west and $east)
			)
		){
			$inset = 0.3125;
		}

		return new AxisAlignedBB(
			$this->x + ($west ? 0 : $inset),
			$this->y,
			$this->z + ($north ? 0 : $inset),
			$this->x + 1 - ($east ? 0 : $inset),
			$this->y + 1.5,
			$this->z + 1 - ($south ? 0 : $inset)
		);
	}
	public function canConnect(Block $block){
		return $block instanceof static or $block instanceof FenceGate or ($block->isSolid() and !$block->isTransparent());
	}
}