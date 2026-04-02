<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\generator\object\Tree;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;

class Sapling extends Flowable{
	const OAK = 0;
	const SPRUCE = 1;
	const BIRCH = 2;
	const JUNGLE = 3;
	const ACACIA = 4;
	const DARK_OAK = 5;

	protected $id = self::SAPLING;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		static $names = [
			0 => "Oak Sapling",
			1 => "Spruce Sapling",
			2 => "Birch Sapling",
			3 => "Jungle Sapling",
			4 => "Acacia Sapling",
			5 => "Dark Oak Sapling"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === self::GRASS or $down->getId() === self::DIRT or $down->getId() === self::FARMLAND or $down->getId() === self::PODZOL){
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}
	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){
			Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->getVariant(), false);

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(mt_rand(1, 7) === 1){
			if(($this->meta & 0x08) === 0x08){
				Tree::growTree($this->getLevel(), $this->x, $this->y, $this->z, new Random(mt_rand()), $this->getVariant());
			}else{
				$this->meta |= 0x08;
				$this->getLevel()->setBlock($this, $this, true);
			}
		}
	}

	public function getVariantBitmask() : int{
		return 0x07;
	}
	public function getDrops(Item $item) : array{
		return [
			[$this->id, $this->meta & 0x07, 1],
		];
	}
}
