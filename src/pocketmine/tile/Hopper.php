<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\tile;

use pocketmine\block\Hopper as HopperBlock;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\inventory\HopperInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\ShulkerBoxInventory;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class Hopper extends Spawnable implements InventoryHolder, Container, Nameable{
	protected $inventory;
	protected $isLocked = false;
	protected $isPowered = false;
	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->TransferCooldown) or !($nbt->TransferCooldown instanceof IntTag)){
			$nbt->TransferCooldown = new IntTag("TransferCooldown", 0);
		}

		parent::__construct($level, $nbt);

		$this->inventory = new HopperInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof ListTag)){
			$this->namedtag->Items = new ListTag("Items", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i), false);
		}

		$this->scheduleUpdate();
	}

	public function close(){
		if($this->closed === false){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}

	public function activate(){
		$this->isPowered = true;
	}

	public function deactivate(){
		$this->isPowered = false;
	}
	public function canUpdate(){
		return $this->namedtag->TransferCooldown->getValue() === 0 and !$this->isPowered;
	}

	public function resetCooldownTicks(){
		$this->namedtag->TransferCooldown->setValue(8);
	}
	public function onUpdate(){
		if(!($this->getBlock() instanceof HopperBlock)){
			return false;
		}
		$area = clone $this->getBlock()->getBoundingBox();
		$area->maxY = ceil($area->maxY) + 1;
		foreach($this->getLevel()->getChunkEntities($this->getBlock()->x >> 4, $this->getBlock()->z >> 4) as $entity){
			if(!($entity instanceof DroppedItem) or !$entity->isAlive()){
				continue;
			}
			if(!$entity->boundingBox->intersectsWith($area)){
				continue;
			}

			$item = $entity->getItem();
			if(!$item instanceof Item){
				continue;
			}
			if($item->getCount() < 1){
				$entity->close();
				continue;
			}

			if($this->inventory->canAddItem($item)){
				$this->inventory->addItem($item);
				$entity->close();
			}
		}

		if(!$this->canUpdate()){
			$this->namedtag->TransferCooldown->setValue($this->namedtag->TransferCooldown->getValue() - 1);
			return true;
		}

		$source = $this->getLevel()->getTile($this->getBlock()->getSide(Vector3::SIDE_UP));
		if($source instanceof Tile and $source instanceof InventoryHolder){
			$inventory = $source->getInventory();
			$item = clone $inventory->getItem($inventory->firstOccupied());
			$item->setCount(1);
			if($this->inventory->canAddItem($item)){
				$this->inventory->addItem($item);
				$inventory->removeItem($item);
				$source->getInventory()->getHolder()->saveNBT();
				$this->resetCooldownTicks();
				if($source instanceof Hopper){
					$source->resetCooldownTicks();
				}
			}
		}

		if(!($this->getLevel()->getTile($this->getBlock()->getSide(Vector3::SIDE_DOWN)) instanceof Hopper)){
			$target = $this->getLevel()->getTile($this->getBlock()->getSide($this->getBlock()->getDamage()));
			if($target instanceof Tile and $target instanceof InventoryHolder){
				$inv = $target->getInventory();
				foreach($this->inventory->getContents() as $item){
					if($item->getId() === Item::AIR or $item->getCount() < 1){
						continue;
					}

					$targetItem = clone $item;
					$targetItem->setCount(1);

					if($item->getId() === 218 and $inv instanceof ShulkerBoxInventory){
						return;
					}

					if($inv->canAddItem($targetItem)){
						$this->inventory->removeItem($targetItem);
						$inv->addItem($targetItem);
						$target->getInventory()->getHolder()->saveNBT();
						$this->resetCooldownTicks();
						if($target instanceof Hopper){
							$target->resetCooldownTicks();
						}
						break;
					}

				}
			}
		}

		return true;
	}
	public function getInventory(){
		return $this->inventory;
	}
	public function getSize(){
		return 5;
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

		if($item->getId() === Item::AIR or $item->getCount() <= 0){
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
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if((int) $slot["Slot"] === (int) $index){
				return (int) $i;
			}
		}

		return -1;
	}

	public function saveNBT(){
		$this->namedtag->Items = new ListTag("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}
	public function getName() : string{
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Hopper";
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
	public function hasLock(){
		return isset($this->namedtag->Lock);
	}
	public function setLock(string $itemName = ""){
		if($itemName === ""){
			unset($this->namedtag->Lock);
			return;
		}
		$this->namedtag->Lock = new StringTag("Lock", $itemName);
	}
	public function checkLock(string $key){
		return $this->namedtag->Lock->getValue() === $key;
	}
	public function getSpawnCompound(){
		$c = new CompoundTag("", [
			new StringTag("id", Tile::HOPPER),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z)
		]);

		if($this->hasName()){
			$c->CustomName = $this->namedtag->CustomName;
		}
		if($this->hasLock()){
			$c->Lock = $this->namedtag->Lock;
		}

		return $c;
	}
}
