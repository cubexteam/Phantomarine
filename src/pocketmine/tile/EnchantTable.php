<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\tile;

use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class EnchantTable extends Spawnable implements Nameable{
	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
	}
	public function getName() : string{
		return $this->hasName() ? $this->namedtag->CustomName->getValue() : "Enchanting Table";
	}
	public function hasName() : bool{
		return isset($this->namedtag->CustomName);
	}
	public function setName(string $str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}

		$this->namedtag->CustomName = new StringTag("CustomName", $str);
	}
	public function getSpawnCompound(){
		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::ENCHANT_TABLE),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z)
		]);

		if($this->hasName()){
			$nbt->CustomName = $this->namedtag->CustomName;
		}

		return $nbt;
	}
}
