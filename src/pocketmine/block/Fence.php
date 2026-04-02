<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class Fence extends Transparent{

	const FENCE_OAK = 0;
	const FENCE_SPRUCE = 1;
	const FENCE_BIRCH = 2;
	const FENCE_JUNGLE = 3;
	const FENCE_ACACIA = 4;
	const FENCE_DARKOAK = 5;

	protected $id = self::FENCE;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 2;
	}
	public function getToolType(){
		return Tool::TYPE_AXE;
	}
	public function getBurnChance() : int{
		return 5;
	}
	public function getBurnAbility() : int{
		return 20;
	}
	public function getName() : string{
		static $names = [
			0 => "Oak Fence",
			1 => "Spruce Fence",
			2 => "Birch Fence",
			3 => "Jungle Fence",
			4 => "Acacia Fence",
			5 => "Dark Oak Fence",
			"",
			""
		];
		return $names[$this->meta & 0x07];
	}

	public function getThickness() : float{
		return 0.25;
	}
	protected function recalculateBoundingBox(){
		$width = 0.5 - $this->getThickness() / 2;

		return new AxisAlignedBB(
			$this->x + ($this->canConnect($this->getSide(Vector3::SIDE_WEST)) ? 0 : $width),
			$this->y,
			$this->z + ($this->canConnect($this->getSide(Vector3::SIDE_NORTH)) ? 0 : $width),
			$this->x + 1 - ($this->canConnect($this->getSide(Vector3::SIDE_EAST)) ? 0 : $width),
			$this->y + 1.5,
			$this->z + 1 - ($this->canConnect($this->getSide(Vector3::SIDE_SOUTH)) ? 0 : $width)
		);
	}

	protected function recalculateCollisionBoxes() : array{
		$inset = 0.5 - $this->getThickness() / 2;
		$bbs = [];

		$connectWest = $this->canConnect($this->getSide(Vector3::SIDE_WEST));
		$connectEast = $this->canConnect($this->getSide(Vector3::SIDE_EAST));

		if($connectWest or $connectEast){
			$bbs[] = new AxisAlignedBB(
				$this->x + ($connectWest ? 0 : $inset),
				$this->y,
				$this->z + $inset,
				$this->x + 1 - ($connectEast ? 0 : $inset),
				$this->y + 1.5,
				$this->z + 1 - $inset
			);
		}

		$connectNorth = $this->canConnect($this->getSide(Vector3::SIDE_NORTH));
		$connectSouth = $this->canConnect($this->getSide(Vector3::SIDE_SOUTH));

		if($connectNorth or $connectSouth){
			$bbs[] = new AxisAlignedBB(
				$this->x + $inset,
				$this->y,
				$this->z + ($connectNorth ? 0 : $inset),
				$this->x + 1 - $inset,
				$this->y + 1.5,
				$this->z + 1 - ($connectSouth ? 0 : $inset)
			);
		}

		if(count($bbs) === 0){
			return [
				new AxisAlignedBB(
					$this->x + $inset,
					$this->y,
					$this->z + $inset,
					$this->x + 1 - $inset,
					$this->y + 1.5,
					$this->z + 1 - $inset
				)
			];
		}

		return $bbs;
	}
	public function canConnect(Block $block){
		return $block instanceof static or $block instanceof FenceGate or ($block->isSolid() and !$block->isTransparent());
	}
}