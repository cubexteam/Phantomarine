<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\Player;

class DoublePlant extends Flowable{
	const BITFLAG_TOP = 0x08;

	protected $id = self::DOUBLE_PLANT;

	const SUNFLOWER = 0;
	const LILAC = 1;
	const DOUBLE_TALLGRASS = 2;
	const LARGE_FERN = 3;
	const ROSE_BUSH = 4;
	const PEONY = 5;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function canBeReplaced() : bool{
		return $this->meta === self::DOUBLE_TALLGRASS || $this->meta === self::LARGE_FERN;
	}
	public function getName() : string{
		static $names = [
			0 => "Sunflower",
			1 => "Lilac",
			2 => "Double Tallgrass",
			3 => "Large Fern",
			4 => "Rose Bush",
			5 => "Peony"
		];
		return $names[$this->getVariant()] ?? "";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$id = $block->getSide(Vector3::SIDE_DOWN)->getId();
		if(($id === BlockIds::GRASS || $id === BlockIds::DIRT)
			&& $block->getSide(Vector3::SIDE_UP)->canBeReplaced()
		){
			$this->getLevel()->setBlock($block, $this, false, false);
			$this->getLevel()->setBlock($block->getSide(Vector3::SIDE_UP), BlockFactory::get($this->id, $this->meta | self::BITFLAG_TOP), false, false);

			return true;
		}

		return false;
	}
	public function isValidHalfPlant() : bool{
		if($this->meta & self::BITFLAG_TOP){
			$other = $this->getSide(Vector3::SIDE_DOWN);
		}else{
			$other = $this->getSide(Vector3::SIDE_UP);
		}

		return (
			$other->getId() === $this->getId() and
			($other->getDamage() & 0x07) === ($this->getDamage() & 0x07) and
			($other->getDamage() & self::BITFLAG_TOP) !== ($this->getDamage() & self::BITFLAG_TOP)
		);
	}

	public function onNearbyBlockChange() : void{
		if(!$this->isValidHalfPlant() or (($this->meta & self::BITFLAG_TOP) === 0 and $this->getSide(Vector3::SIDE_DOWN)->isTransparent())){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		if(parent::onBreak($item, $player) and $this->isValidHalfPlant()){
			$this->getLevel()->useBreakOn($this->getSide(($this->meta & self::BITFLAG_TOP) !== 0 ? Vector3::SIDE_DOWN : Vector3::SIDE_UP), $item, $player, $player !== null);
		}

		return false;
	}

	public function getVariantBitmask() : int{
		return 0x07;
	}

	public function getDrops(Item $item) : array{
		$variant = $this->getVariant();
		if($this->meta & self::BITFLAG_TOP){
			if(!$item->isShears() && ($variant === self::DOUBLE_TALLGRASS && $variant === self::LARGE_FERN)){
				if(mt_rand(0, 24) === 0){
					return [Item::get(ItemIds::SEEDS)];
				}

				return [];
			}

			return parent::getDrops($item);
		}

		return [];
	}

	public function getAffectedBlocks() : array{
		if($this->isValidHalfPlant()){
			return [$this, $this->getSide(($this->meta & self::BITFLAG_TOP) !== 0 ? Vector3::SIDE_DOWN : Vector3::SIDE_UP)];
		}

		return parent::getAffectedBlocks();
	}
}