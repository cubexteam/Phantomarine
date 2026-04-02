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
use pocketmine\utils\Color;

class Cauldron extends Spawnable{
	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->PotionId) or !($nbt->PotionId instanceof IntTag)){
			$nbt->PotionId = new IntTag("PotionId", 0xffff);
		}
		if(!isset($nbt->SplashPotion) or !($nbt->SplashPotion instanceof ByteTag)){
			$nbt->SplashPotion = new ByteTag("SplashPotion", 0);
		}
		parent::__construct($level, $nbt);
	}
	public function getPotionId(){
		return $this->namedtag["PotionId"];
	}
	public function setPotionId($potionId){
		$this->namedtag->PotionId = new IntTag("PotionId", $potionId);
		$this->onChanged();
	}
	public function hasPotion(){
		return $this->namedtag["PotionId"] !== 0xffff;
	}
	public function getSplashPotion(){
		return ($this->namedtag["SplashPotion"] == true);
	}
	public function setSplashPotion($bool){
		$this->namedtag->SplashPotion = new ByteTag("SplashPotion", ($bool == true) ? 1 : 0);
		$this->onChanged();
	}
	public function getCustomColor(){
		if($this->isCustomColor()){
			$color = $this->namedtag["CustomColor"];
			$green = ($color >> 8) & 0xff;
			$red = ($color >> 16) & 0xff;
			$blue = ($color) & 0xff;
			return Color::getRGB($red, $green, $blue);
		}
		return null;
	}
	public function getCustomColorRed(){
		return ($this->namedtag["CustomColor"] >> 16) & 0xff;
	}
	public function getCustomColorGreen(){
		return ($this->namedtag["CustomColor"] >> 8) & 0xff;
	}
	public function getCustomColorBlue(){
		return ($this->namedtag["CustomColor"]) & 0xff;
	}
	public function isCustomColor(){
		return isset($this->namedtag->CustomColor);
	}
	public function setCustomColor($r, $g = 0xff, $b = 0xff){
		if($r instanceof Color){
			$color = ($r->getRed() << 16 | $r->getGreen() << 8 | $r->getBlue()) & 0xffffff;
		}else{
			$color = ($r << 16 | $g << 8 | $b) & 0xffffff;
		}
		$this->namedtag->CustomColor = new IntTag("CustomColor", $color);
		$this->onChanged();
	}

	public function clearCustomColor(){
		if(isset($this->namedtag->CustomColor)){
			unset($this->namedtag->CustomColor);
		}
		$this->onChanged();
	}
	public function getSpawnCompound(){
		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::CAULDRON),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			new IntTag("PotionId", $this->namedtag["PotionId"]),
			new ByteTag("SplashPotion", $this->namedtag["SplashPotion"])
		]);

		if($this->getPotionId() === 0xffff and $this->isCustomColor()){
			$nbt->CustomColor = $this->namedtag->CustomColor;
		}
		return $nbt;
	}
}
