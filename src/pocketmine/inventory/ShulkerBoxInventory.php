<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\ShulkerBox;

class ShulkerBoxInventory extends ContainerInventory{
	protected $holder;
	public function __construct(ShulkerBox $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::SHULKER_BOX));
	}
	public function getName() : string{
		return "Shulker Box";
	}
	public function getDefaultSize() : int{
		return 27;
	}
	public function getNetworkType() : int{
		return WindowTypes::CONTAINER;
	}
	public function getHolder(){
		return $this->holder;
	}
	public function canAddItem(Item $item){
		if($item->getId() === Block::UNDYED_SHULKER_BOX || $item->getId() === Block::SHULKER_BOX){
			return false;
		}
		return parent::canAddItem($item);
	}

	public function onOpen(Player $who){
		parent::onOpen($who);
		if(count($this->getViewers()) === 1){
			$pk = new BlockEventPacket();
			$pk->x = $this->getHolder()->getFloorX();
			$pk->y = $this->getHolder()->getFloorY();
			$pk->z = $this->getHolder()->getFloorZ();
			$pk->case1 = 1;
			$pk->case2 = 2;
			if(($level = $this->getHolder()->getLevel()) instanceof Level){
				$level->broadcastLevelSoundEvent($this->getHolder(), LevelSoundEventPacket::SOUND_SHULKERBOX_OPEN);
				$level->addChunkPacket($this->getHolder()->getFloorX() >> 4, $this->getHolder()->getFloorZ() >> 4, $pk);
			}
		}
	}

	public function onClose(Player $who){
		if(count($this->getViewers()) === 1){
			$pk = new BlockEventPacket();
			$pk->x = $this->getHolder()->getFloorX();
			$pk->y = $this->getHolder()->getFloorY();
			$pk->z = $this->getHolder()->getFloorZ();
			$pk->case1 = 1;
			$pk->case2 = 0;
			if(($level = $this->getHolder()->getLevel()) instanceof Level){
				$level->broadcastLevelSoundEvent($this->getHolder(), LevelSoundEventPacket::SOUND_SHULKERBOX_CLOSED);
				$level->addChunkPacket($this->getHolder()->getFloorX() >> 4, $this->getHolder()->getFloorZ() >> 4, $pk);
			}
		}
		$this->getHolder()->saveNBT();
		parent::onClose($who);
	}
}