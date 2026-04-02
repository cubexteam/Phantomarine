<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Torch extends Flowable{

	protected $id = self::TORCH;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getLightLevel(){
		return 14;
	}
	public function getName() : string{
		return "Torch";
	}

	public function onNearbyBlockChange() : void{
		$below = $this->getSide(Vector3::SIDE_DOWN);
		$side = $this->getDamage();
		$faces = [
			0 => Vector3::SIDE_DOWN,
			1 => Vector3::SIDE_WEST,
			2 => Vector3::SIDE_EAST,
			3 => Vector3::SIDE_NORTH,
			4 => Vector3::SIDE_SOUTH,
			5 => Vector3::SIDE_DOWN
		];

		if($this->getSide($faces[$side])->isTransparent() and !($side === Vector3::SIDE_DOWN and ($below->getId() === self::FENCE or $below->getId() === self::COBBLESTONE_WALL))){
			$this->getLevel()->useBreakOn($this);
		}
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$below = $this->getSide(Vector3::SIDE_DOWN);

		if($target->isTransparent() === false and $face !== 0){
			$faces = [
				1 => 5,
				2 => 4,
				3 => 3,
				4 => 2,
				5 => 1,
			];
			$this->meta = $faces[$face];
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}elseif(
			$below->isTransparent() === false or $below->getId() === self::FENCE or
			$below->getId() === self::COBBLE_WALL or
			$below->getId() == Block::REDSTONE_LAMP or
			$below->getId() == Block::LIT_REDSTONE_LAMP
		){
			$this->meta = 0;
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}
	public function getDrops(Item $item) : array{
		return [
			[$this->id, 0, 1],
		];
	}
}