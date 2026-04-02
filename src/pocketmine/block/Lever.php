<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\sound\ButtonClickSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Lever extends RedstoneSource{
	protected $id = self::LEVER;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Lever";
	}
	public function onNearbyBlockChange() : void{
		$side = $this->getDamage();
		if($this->isActivated()) $side ^= 0x08;
		$faces = [
			0 => Vector3::SIDE_UP,
			1 => Vector3::SIDE_WEST,
			2 => Vector3::SIDE_EAST,
			3 => Vector3::SIDE_NORTH,
			4 => Vector3::SIDE_SOUTH,
			5 => Vector3::SIDE_DOWN,
			6 => Vector3::SIDE_DOWN,
			7 => Vector3::SIDE_UP
		];
		if(!$this->getSide($faces[$this->meta & 0x07])->isSolid()){
			$this->level->useBreakOn($this);
		}
	}

	public function getVariantBitmask() : int{
		return 0;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$faces = [
				3 => 3,
				2 => 4,
				4 => 2,
				5 => 1,
			];
			if($face === 0){
				$to = $player instanceof Player ? $player->getDirection() : 0;
				$this->meta = ($to % 2 != 1 ? 0 : 7);
			}elseif($face === 1){
				$to = $player instanceof Player ? $player->getDirection() : 0;
				$this->meta = ($to % 2 != 1 ? 6 : 5);
			}else{
				$this->meta = $faces[$face];
			}

			$this->getLevel()->setBlock($block, $this, true, false);
			return true;
		}

		return false;
	}
	public function activate(array $ignore = []){
		parent::activate($ignore);
		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;
		$faces = [
			5 => 0,
			6 => 0,
			3 => 2,
			1 => 4,
			4 => 3,
			2 => 5,
			0 => 1,
			7 => 1,
		];

		$block = $this->getSide($faces[$side])->getSide(Vector3::SIDE_UP);
		if(!$this->equals($block)){
			$this->activateBlock($block);
		}

		$this->checkTorchOn($this->getSide($faces[$side]), [static::getOppositeSide($faces[$side])]);
	}
	public function deactivate(array $ignore = []){
		parent::deactivate($ignore);
		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;
		$faces = [
			5 => 0,
			6 => 0,
			3 => 2,
			1 => 4,
			4 => 3,
			2 => 5,
			0 => 1,
			7 => 1,
		];

		$block = $this->getSide($faces[$side])->getSide(Vector3::SIDE_UP);
		if(!$this->equals($block)){
			$this->deactivateBlock($block);
		}

		$this->checkTorchOff($this->getSide($faces[$side]), [static::getOppositeSide($faces[$side])]);
	}
	public function onActivate(Item $item, Player $player = null){
		$this->meta ^= 0x08;
		$this->getLevel()->setBlock($this, $this, true, false);
		$this->getLevel()->addSound(new ButtonClickSound($this));
		if($this->isActivated()) $this->activate();
		else $this->deactivate();
		return true;
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		if($this->isActivated()){
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, false);
			$this->deactivate();
		}
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, false);
		return true;
	}
	public function isActivated(Block $from = null){
		return (($this->meta & 0x08) === 0x08);
	}
	public function getHardness(){
		return 0.5;
	}
	public function getResistance(){
		return 2.5;
	}
	public function getDrops(Item $item) : array{
		return [
			[$this->id, 0, 1],
		];
	}
}