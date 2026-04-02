<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\ContainerSetContentPacket;
use pocketmine\network\mcpe\protocol\ContainerSetSlotPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\Player;
use pocketmine\Server;

class PlayerInventory extends BaseInventory{

	protected $itemInHandIndex = 0;
	protected $hotbar;
	public function __construct(Human $player, $contents = null){
		$this->hotbar = range(0, $this->getHotbarSize() - 1, 1);
		parent::__construct($player, InventoryType::get(InventoryType::PLAYER));

		if($contents !== null){
			if($contents instanceof ListTag){
				foreach($contents as $item){
					if($item["Slot"] >= 0 and $item["Slot"] < $this->getHotbarSize()){
						if(isset($item["TrueSlot"])){

							if(0 <= $item["TrueSlot"] and $item["TrueSlot"] < $this->getSize()){
								$this->hotbar[$item["Slot"]] = $item["TrueSlot"];

							}elseif($item["TrueSlot"] < 0){
								$this->hotbar[$item["Slot"]] = -1;
							}
						}
						/* If TrueSlot is not set, leave the slot index as its default which was filled in above
						 * This only overwrites slot indexes for valid links */
					}elseif($item["Slot"] >= 100 and $item["Slot"] < 104){
						$this->setItem($this->getSize() + $item["Slot"] - 100, Item::nbtDeserialize($item), false);
					}else{
						$this->setItem($item["Slot"] - $this->getHotbarSize(), Item::nbtDeserialize($item), false);
					}
				}
			}else{
				throw new \InvalidArgumentException("Expecting ListTag, received " . gettype($contents));
			}
		}
	}
	public function getSize(){
		return parent::getSize() - 4;
	}
	public function setSize($size){
		parent::setSize($size + 4);
		$this->sendContents($this->getViewers());
	}
	public function getHotbarSlotIndex($index){
		return ($index >= 0 and $index < $this->getHotbarSize()) ? $this->hotbar[$index] : -1;
	}
	public function setHotbarSlotIndex($index, $slot){
		trigger_error("Do not attempt to change hotbar links in plugins!", E_USER_DEPRECATED);
	}
	public function getHeldItemIndex(){
		return $this->itemInHandIndex;
	}
	public function setHeldItemIndex($hotbarSlotIndex, $sendToHolder = true, $slotMapping = null){
		if($slotMapping !== null){
			$slotMapping -= $this->getHotbarSize();
		}
		if(0 <= $hotbarSlotIndex and $hotbarSlotIndex < $this->getHotbarSize()){
			$this->itemInHandIndex = $hotbarSlotIndex;
			if($slotMapping !== null){
				/* Handle a hotbar slot mapping change. This allows PE to select different inventory slots.
				 * This is the only time slot mapping should ever be changed. */

				if($slotMapping < 0 or $slotMapping >= $this->getSize()){
					$slotMapping = -1;
				}

				$item = $this->getItem($slotMapping);
				if($this->getHolder() instanceof Player){
					Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerItemHeldEvent($this->getHolder(), $item, $slotMapping, $hotbarSlotIndex));
					if($ev->isCancelled()){
						$this->sendHeldItem($this->getHolder());
						$this->sendContents($this->getHolder());
						return;
					}
				}

				if(($key = array_search($slotMapping, $this->hotbar)) !== false and $slotMapping !== -1){
					/* Do not do slot swaps if the slot was null
					 * Chosen slot is already linked to a hotbar slot, swap the two slots around.
					 * This will already have been done on the client-side so no changes need to be sent. */
					$this->hotbar[$key] = $this->hotbar[$this->itemInHandIndex];
				}

				$this->hotbar[$this->itemInHandIndex] = $slotMapping;
			}
			$this->sendHeldItem($this->getHolder()->getViewers());
			if($sendToHolder){
				$this->sendHeldItem($this->getHolder());
			}
		}
	}
	public function getItemInHand(){
		$item = $this->getItem($this->getHeldItemSlot());
		if($item instanceof Item){
			return $item;
		}else{
			return Item::get(Item::AIR, 0, 0);
		}
	}
	public function setItemInHand(Item $item){
		return $this->setItem($this->getHeldItemSlot(), $item);
	}
	public function getHotbar(){
		return $this->hotbar;
	}
	public function getHeldItemSlot(){
		return $this->getHotbarSlotIndex($this->itemInHandIndex);
	}
	public function setHeldItemSlot($slot){
	}
	public function sendHeldItem($target){
		$item = $this->getItemInHand();

		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->item = $item;
		$pk->inventorySlot = $this->getHeldItemSlot();
		$pk->hotbarSlot = $this->getHeldItemIndex();
		$pk->windowId = ContainerIds::INVENTORY;

		if(!is_array($target)){
			$target->dataPacket($pk);
			if($this->getHeldItemSlot() !== -1 and $target === $this->getHolder()){
				$this->sendSlot($this->getHeldItemSlot(), $target);
			}
		}else{
			$this->getHolder()->getLevel()->getServer()->broadcastPacket($target, $pk);
			if($this->getHeldItemSlot() !== -1 and in_array($this->getHolder(), $target, true)){
				$this->sendSlot($this->getHeldItemSlot(), $this->getHolder());
			}
		}
	}
	public function onSlotChange($index, $before, $send){
		$holder = $this->getHolder();
		if(!$holder instanceof Player or !$holder->spawned){
			return;
		}

		if($index >= $this->getSize()){
			$this->sendArmorSlot($index, $this->getHolder()->getViewers());
			if($send){
				$this->sendArmorSlot($index, $this->getViewers());
			}
		}else{
			parent::onSlotChange($index, $before, $send);
		}
	}
	public function getHotbarSize(){
		return 9;
	}
	public function getArmorItem($index){
		return $this->getItem($this->getSize() + $index);
	}
	public function setArmorItem($index, Item $item){
		return $this->setItem($this->getSize() + $index, $item);
	}
	public function damageArmor($index, $cost){
		$itemIndex = $this->getSize() + $index;

		$this->slots[$itemIndex]->useOn($this->slots[$itemIndex]);
		if($this->slots[$itemIndex]->getDamage() >= $this->slots[$itemIndex]->getMaxDurability()){
			$this->setItem($itemIndex, Item::get(Item::AIR, 0, 0));
		}

		$this->sendArmorContents($this->getViewers());
	}
	public function getHelmet(){
		return $this->getItem($this->getSize());
	}
	public function getChestplate(){
		return $this->getItem($this->getSize() + 1);
	}
	public function getLeggings(){
		return $this->getItem($this->getSize() + 2);
	}
	public function getBoots(){
		return $this->getItem($this->getSize() + 3);
	}
	public function setHelmet(Item $helmet){
		return $this->setItem($this->getSize(), $helmet);
	}
	public function setChestplate(Item $chestplate){
		return $this->setItem($this->getSize() + 1, $chestplate);
	}
	public function setLeggings(Item $leggings){
		return $this->setItem($this->getSize() + 2, $leggings);
	}
	public function setBoots(Item $boots){
		return $this->setItem($this->getSize() + 3, $boots);
	}
	public function setItem($index, Item $item, $send = true){
		if($index < 0 or $index >= $this->size){
			return false;
		}elseif($item->getId() === 0 or $item->getCount() <= 0){
			return $this->clear($index, $send);
		}

		if($index >= $this->getSize()){
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled() and $this->getHolder() instanceof Human){
				$this->sendArmorSlot($index, $this->getViewers());
				return false;
			}
			$item = $ev->getNewItem();
		}else{
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled()){
				$this->sendSlot($index, $this->getViewers());
				return false;
			}
			$item = $ev->getNewItem();
		}


		$old = $this->getItem($index);
		$this->slots[$index] = clone $item;
		$this->onSlotChange($index, $old, $send);

		return true;
	}
	public function clear($index, $send = true){
		if(isset($this->slots[$index])){
			$item = Item::get(Item::AIR, 0, 0);
			$old = $this->slots[$index];
			if($index >= $this->getSize() and $index < $this->size){
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getViewers());
					}else{
						$this->sendSlot($index, $this->getViewers());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}else{
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getViewers());
					}else{
						$this->sendSlot($index, $this->getViewers());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}
			if($item->getId() !== Item::AIR){
				$this->slots[$index] = clone $item;
			}else{
				unset($this->slots[$index]);
			}

			$this->onSlotChange($index, $old, $send);
		}

		return true;
	}
	public function getArmorContents(){
		$armor = [];

		for($i = 0; $i < 4; ++$i){
			$armor[$i] = $this->getItem($this->getSize() + $i);
		}

		return $armor;
	}

	public function clearAll($send = true){
		parent::clearAll($send);

		for($i = $this->getSize(), $m = $i + 4; $i < $m; ++$i){
			$this->clear($i, false);
		}

		$this->hotbar = range(0, $this->getHotbarSize() - 1, 1);
		if($send){
			$this->sendArmorContents($this->getViewers());
		}
	}
	public function sendArmorContents($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getArmorContents();

		$pk = new MobArmorEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->slots = $armor;
		$pk->encode();
		$pk->isEncoded = true;

		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk2 = new ContainerSetContentPacket();
				$pk2->windowid = ContainerIds::ARMOR;
				$pk2->slots = $armor;
				$pk2->targetEid = $player->getId();
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
	}
	public function setArmorContents(array $items){
		for($i = 0; $i < 4; ++$i){
			if(!isset($items[$i]) or !($items[$i] instanceof Item)){
				$items[$i] = Item::get(Item::AIR, 0, 0);
			}


			$this->setItem($this->getSize() + $i, $items[$i], false);
		}

		$this->sendArmorContents($this->getViewers());
	}
	public function sendArmorSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getArmorContents();

		$pk = new MobArmorEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->slots = $armor;
		$pk->encode();
		$pk->isEncoded = true;

		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk2 = new ContainerSetSlotPacket();
				$pk2->windowid = ContainerIds::ARMOR;
				$pk2->slot = $index - $this->getSize();
				$pk2->item = $this->getItem($index);
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
	}
	public function sendContents($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetContentPacket();
		$pk->slots = [];
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$pk->slots[$i] = $this->getItem($i);
		}

		for($i = $this->getSize(); $i < $this->getSize() + $this->getHotbarSize(); ++$i){
			$pk->slots[$i] = Item::get(Item::AIR, 0, 0);
		}

		foreach($target as $player){
			$pk->hotbar = [];
			if($player === $this->getHolder()){
				for($i = 0; $i < $this->getHotbarSize(); ++$i){
					$index = $this->getHotbarSlotIndex($i);
					$pk->hotbar[$i] = $index <= -1 ? -1 : $index + $this->getHotbarSize();
				}
			}
			if(($id = $player->getWindowId($this)) === -1 or $player->spawned !== true){
				$this->close($player);
				continue;
			}
			$pk->windowid = $id;
			$pk->targetEid = $player->getId();
			$player->dataPacket(clone $pk);
		}
	}

    public function sendCreativeContents(){
        $pk = new ContainerSetContentPacket();
        $pk->windowid = ContainerIds::CREATIVE;

        if(!$this->getHolder()->isSpectator()){
            foreach(Item::getCreativeItems() as $i => $item){
                $pk->slots[$i] = clone $item;
            }
        }
        $pk->targetEid = $this->getHolder()->getId();
        $this->getHolder()->dataPacket($pk);
    }
	public function sendSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetSlotPacket();
		$pk->slot = $index;
		$pk->item = $this->getItem($index);

		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk->windowid = 0;
				$player->dataPacket(clone $pk);
			}else{
				if(($id = $player->getWindowId($this)) === -1){
					$this->close($player);
					continue;
				}
				$pk->windowid = $id;
				$player->dataPacket(clone $pk);
			}
		}
	}
	public function getHolder(){
		return parent::getHolder();
	}

}