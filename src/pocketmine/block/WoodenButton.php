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

class WoodenButton extends RedstoneSource{
	protected $id = self::WOODEN_BUTTON;
	public function __construct($meta = 5){
		$this->meta = $meta;
	}

	public function onScheduledUpdate() : void{
		if ($this->isActivated()) {
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, false);
			$this->getLevel()->addSound(new ButtonClickSound($this));
			$this->deactivate();
		}
	}

	public function onNearbyBlockChange() : void{
		$side = $this->getDamage();
		if($this->isActivated()) $side ^= 0x08;
		$faces = [
			0 => 1,
			1 => 0,
			2 => 3,
			3 => 2,
			4 => 5,
			5 => 4,
		];

		if (isset($faces[$side])) {
			if($this->getSide($faces[$side]) instanceof Transparent){
				$this->getLevel()->useBreakOn($this);
			}
		}
	}
	public function deactivate(array $ignore = []){
		parent::deactivate($ignore = []);
		$faces = [
			0 => 1,
			1 => 0,
			2 => 3,
			3 => 2,
			4 => 5,
			5 => 4,
		];
		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;

		$block = $this->getSide($faces[$side])->getSide(Vector3::SIDE_UP);
		if(!$this->equals($block)){
			$this->deactivateBlock($block);
		}

		if($side != 1){
			$this->deactivateBlock($this->getSide($faces[$side], 2));
		}

		$this->checkTorchOff($this->getSide($faces[$side]), [static::getOppositeSide($faces[$side])]);
	}
	public function activate(array $ignore = []){
		parent::activate($ignore = []);
		$faces = [
			0 => 1,
			1 => 0,
			2 => 3,
			3 => 2,
			4 => 5,
			5 => 4,
		];

		$side = $this->meta;
		if($this->isActivated()) $side ^= 0x08;

		$block = $this->getSide($faces[$side])->getSide(Vector3::SIDE_UP);
		if(!$this->equals($block)){
			$this->activateBlock($block);
		}

		if($side != 1){
			$block = $this->getSide($faces[$side], 2);
			$this->activateBlock($block);
		}

		$this->checkTorchOn($this->getSide($faces[$side]), [static::getOppositeSide($faces[$side])]);
	}
	public function getName() : string{
		return "Wooden Button";
	}
	public function getHardness(){
		return 0.5;
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
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$this->meta = $face;
			$this->getLevel()->setBlock($block, $this, true, false);
			return true;
		}
		return false;
	}
	public function isActivated(Block $from = null){
		return (($this->meta & 0x08) === 0x08);
	}
	public function onActivate(Item $item, Player $player = null){
		if(!$this->isActivated()){
			$this->meta ^= 0x08;
			$this->getLevel()->setBlock($this, $this, true, false);
			$this->getLevel()->addSound(new ButtonClickSound($this));
			$this->activate();
			$this->getLevel()->scheduleUpdate($this, 30);
		}
		return true;
	}

	public function getVariant() : int{
		return 5;
	}
}
