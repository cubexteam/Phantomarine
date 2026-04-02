<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\tile;

use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class Bed extends Spawnable{
	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->color) or !($nbt->color instanceof ByteTag)){
			$nbt->color = new ByteTag("color", 14);
		}
		parent::__construct($level, $nbt);
	}
	public function getColor() : int{
		return $this->namedtag->color->getValue();
	}
	public function setColor(int $color){
		$this->namedtag["color"] = $color & 0x0f;
		$this->onChanged();
	}
	public function getSpawnCompound(){
		return new CompoundTag("", [
			new StringTag("id", Tile::BED),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			$this->namedtag->color
		]);
	}

}