<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class RedstoneTorch extends RedstoneSource{

	protected $id = self::REDSTONE_TORCH;
	protected $ignore = "";
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getLightLevel(){
		return 7;
	}
	public function getLastUpdateTime(){
		return $this->getLevel()->getBlockTempData($this);
	}

	public function setLastUpdateTimeNow(){
		$this->getLevel()->setBlockTempData($this, $this->getLevel()->getServer()->getTick());
	}
	public function canCalcTurn(){
		if(!parent::canCalc()) return false;
		if($this->getLevel()->getServer()->getTick() != $this->getLastUpdateTime()) return true;
		return $this->canScheduleUpdate();
	}
	public function canScheduleUpdate(){
		return $this->getLevel()->getServer()->allowFrequencyPulse;
	}
	public function getFrequency(){
		return $this->getLevel()->getServer()->pulseFrequency;
	}
	public function getName() : string{
		return "Redstone Torch";
	}
	public function turnOn($ignore = ""){
		$result = $this->canCalcTurn();
		$this->setLastUpdateTimeNow();
		if($result === true){
			$faces = [
				1 => 4,
				2 => 5,
				3 => 2,
				4 => 3,
				5 => 0,
				6 => 0,
				0 => 0,
			];
			$this->id = self::REDSTONE_TORCH;
			$this->getLevel()->setBlock($this, $this, true);
			$this->activateTorch([$faces[$this->meta]], [$ignore]);
			return true;
		}elseif($result === 3){
			$this->ignore = $ignore;
			$this->getLevel()->scheduleUpdate($this, 20 * $this->getFrequency());
			return true;
		}
		return false;
	}
	public function turnOff($ignore = ""){
		$result = $this->canCalcTurn();
		$this->setLastUpdateTimeNow();
		if($result === true){
			$faces = [
				1 => 4,
				2 => 5,
				3 => 2,
				4 => 3,
				5 => 0,
				6 => 0,
				0 => 0,
			];
			$this->id = self::UNLIT_REDSTONE_TORCH;
			$this->getLevel()->setBlock($this, $this, true);
			$this->deactivateTorch([$faces[$this->meta]], [$ignore]);
			return true;
		}elseif($result === 3){
			$this->ignore = $ignore;
			$this->getLevel()->scheduleUpdate($this, 20 * $this->getFrequency());
			return true;
		}
		return false;
	}
	public function activateTorch(array $ignore = [], $notCheck = []){
		if($this->canCalc()){
			$this->activated = true;

			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH, Vector3::SIDE_UP, Vector3::SIDE_DOWN];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					$block = $this->getSide($side);
					if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
						$this->activateBlock($block);
					}
				}
			}
		}
	}
	public function activate(array $ignore = []){
		$this->activateTorch($ignore);
	}
	public function deactivate(array $ignore = []){
		$this->deactivateTorch($ignore);
	}
	public function deactivateTorch(array $ignore = [], array $notCheck = []){
		if($this->canCalc()){
			$this->activated = false;

			$sides = [Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH];

			foreach($sides as $side){
				if(!in_array($side, $ignore)){
					$block = $this->getSide($side);
					if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
						$this->deactivateBlock($block);
					}
				}
			}

			if(!in_array(Vector3::SIDE_DOWN, $ignore)){
				$block = $this->getSide(Vector3::SIDE_DOWN);
				if(!in_array($hash = Level::blockHash($block->x, $block->y, $block->z), $notCheck)){
					if(!$this->checkPower($block)){
						if($block->getId() == Block::LIT_REDSTONE_LAMP) $block->turnOff();
					}

					$block = $this->getSide(Vector3::SIDE_DOWN, 2);
					$this->deactivateBlock($block);
				}
			}
		}
	}

	public function onNearbyBlockChange() : void{
		$faces = [
			1 => 4,
			2 => 5,
			3 => 2,
			4 => 3,
			5 => 0,
			6 => 0,
			0 => 0,
		];

		$below = $this->getSide(Vector3::SIDE_DOWN);
		$side = $this->getDamage();

		if($this->getSide($faces[$side])->isTransparent() === true and
			!($side === 0 and ($below->getId() === self::FENCE or
					$below->getId() === self::COBBLE_WALL
				))
		){
			$this->getLevel()->useBreakOn($this);

		}
		$this->activate([$faces[$side]]);
	}

	public function onScheduledUpdate() : void{
		if($this->id == self::UNLIT_REDSTONE_TORCH){
			$this->turnOn($this->ignore);
		}else{
			$this->turnOff($this->ignore);
		}
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, false);
		$faces = [
			1 => 4,
			2 => 5,
			3 => 2,
			4 => 3,
			5 => 0,
			6 => 0,
			0 => 0,
		];
		$this->deactivate([$faces[$this->meta]]);
		$this->getLevel()->setBlockTempData($this);

		return true;
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
			[Item::LIT_REDSTONE_TORCH, 0, 1],
		];
	}
	public function isActivated(Block $from = null){
		return true;
	}
}
