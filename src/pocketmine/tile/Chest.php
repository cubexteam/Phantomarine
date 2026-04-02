<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class Chest extends Spawnable implements InventoryHolder, Container, Nameable{
	protected $inventory;
	protected $doubleInventory = null;
	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->inventory = new ChestInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof ListTag)){
			$this->namedtag->Items = new ListTag("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i), false);
		}

		if (!isset($this->namedtag->trapped)){
			$blockTile = $this->level->getBlockAt($this->x, $this->y, $this->z);
			if($blockTile->getId() === Block::TRAPPED_CHEST){
				$this->namedtag->trapped = new ByteTag("trapped", 1);
			}else{
				$this->namedtag->trapped = new ByteTag("trapped", 0);
			}
		}
	}

	public function close(){
		if(!$this->closed){
			$this->inventory->removeAllViewers(true);

			if($this->doubleInventory !== null){
				if($this->isPaired() and $this->level->isChunkLoaded($this->namedtag->pairx->getValue() >> 4, $this->namedtag->pairz->getValue() >> 4)){
					$this->doubleInventory->removeAllViewers(true);
					$this->doubleInventory->invalidate();
					if(($pair = $this->getPair()) !== null){
						$pair->doubleInventory = null;
					}
				}
				$this->doubleInventory = null;
			}

			$this->inventory = null;

			parent::close();
		}
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Items = new ListTag("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}
	public function getSize(){
		return 27;
	}
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if((int) $slot["Slot"] === (int) $index){
				return (int) $i;
			}
		}

		return -1;
	}
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}else{
			return Item::nbtDeserialize($this->namedtag->Items[$i]);
		}
	}
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);

		if($item->isNull()){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $item->nbtSerialize($index);
		}else{
			$this->namedtag->Items[$i] = $item->nbtSerialize($index);
		}

		return true;
	}
	public function getInventory(){
		if($this->isPaired() and $this->doubleInventory === null){
			$this->checkPairing();
		}
		return $this->doubleInventory instanceof DoubleChestInventory ? $this->doubleInventory : $this->inventory;
	}
	public function getRealInventory(){
		return $this->inventory;
	}
	public function getDoubleInventory(){
		return $this->doubleInventory;
	}

	protected function checkPairing(){
		if($this->isPaired() and !$this->getLevel()->isInLoadedTerrain(new Vector3($this->namedtag->pairx->getValue(), $this->y, $this->namedtag->pairz->getValue()))){
			$this->doubleInventory = null;

		}elseif(($pair = $this->getPair()) instanceof Chest){
			if(!$pair->isPaired()){
				$pair->createPair($this);
				$pair->checkPairing();
			}
			if($this->doubleInventory === null){
				if($pair->doubleInventory !== null){
					$this->doubleInventory = $pair->doubleInventory;
				}else{
					if(($pair->x + ($pair->z << 15)) > ($this->x + ($this->z << 15))){
						$this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($pair, $this);
					}else{
						$this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($this, $pair);
					}
				}
			}
		}else{
			$this->doubleInventory = null;
			unset($this->namedtag->pairx, $this->namedtag->pairz);
		}
	}
	public function getName() : string{
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Chest";
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
	public function isPaired(){
		return isset($this->namedtag->pairx) and isset($this->namedtag->pairz);
	}

	public function isTrapped(){
		return $this->namedtag->trapped->getValue();
	}
	public function getPair(){
		if($this->isPaired()){
			$tile = $this->getLevel()->getTileAt($this->namedtag->pairx->getValue(), $this->y, $this->namedtag->pairz->getValue());
			if($tile instanceof Chest){
				return $tile;
			}
		}

		return null;
	}
	public function pairWith(Chest $tile){
		if($this->isPaired() or $tile->isPaired()){
			return false;
		}

		$this->createPair($tile);

		$this->onChanged();
		$tile->onChanged();
		$this->checkPairing();

		return true;
	}
	private function createPair(Chest $tile){
		$this->namedtag->pairx = new IntTag("pairx", $tile->x);
		$this->namedtag->pairz = new IntTag("pairz", $tile->z);

		$tile->namedtag->pairx = new IntTag("pairx", $this->x);
		$tile->namedtag->pairz = new IntTag("pairz", $this->z);
	}
	public function unpair(){
		if(!$this->isPaired()){
			return false;
		}

		$tile = $this->getPair();
		unset($this->namedtag->pairx, $this->namedtag->pairz);

		$this->onChanged();

		if($tile instanceof Chest){
			unset($tile->namedtag->pairx, $tile->namedtag->pairz);
			$tile->checkPairing();
			$tile->onChanged();
		}
		$this->checkPairing();

		return true;
	}
	public function getSpawnCompound(){
		if($this->isPaired()){
			$c = new CompoundTag("", [
				new StringTag("id", Tile::CHEST),
				new IntTag("x", (int) $this->x),
				new IntTag("y", (int) $this->y),
				new IntTag("z", (int) $this->z),
				new IntTag("pairx", (int) $this->namedtag["pairx"]),
				new IntTag("pairz", (int) $this->namedtag["pairz"])
			]);
		}else{
			$c = new CompoundTag("", [
				new StringTag("id", Tile::CHEST),
				new IntTag("x", (int) $this->x),
				new IntTag("y", (int) $this->y),
				new IntTag("z", (int) $this->z)
			]);
		}

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}

		return $c;
	}
}