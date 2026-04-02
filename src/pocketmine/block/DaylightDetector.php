<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\DLDetector;
use pocketmine\tile\Tile;

class DaylightDetector extends RedstoneSource{
	protected $id = self::DAYLIGHT_SENSOR;
	public function getName() : string{
		return "Daylight Sensor";
	}
	public function getBoundingBox(){
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}
	public function canBeFlowedInto(){
		return false;
	}
	protected function getTile(){
		$t = $this->getLevel()->getTile($this);
		if($t instanceof DLDetector){
			return $t;
		}else{
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::DAY_LIGHT_DETECTOR),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z)
			]);
			return Tile::createTile(Tile::DAY_LIGHT_DETECTOR, $this->getLevel(), $nbt);
		}
	}
	public function onActivate(Item $item, Player $player = null){
		$this->getLevel()->setBlock($this, new DaylightDetectorInverted(), true, true);
		$this->getTile()->onUpdate();
		return true;
	}
	public function isActivated(Block $from = null){
		return $this->getTile()->isActivated();
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
		if($this->isActivated()) $this->deactivate();
		return true;
	}
	public function getHardness(){
		return 0.2;
	}
	public function getResistance(){
		return 1;
	}
	public function getDrops(Item $item) : array{
		return [
			[self::DAYLIGHT_SENSOR, 0, 1]
		];
	}
}