<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\block\TrappedChest;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\tile\Chest;

class ChestInventory extends ContainerInventory{
	public function __construct(Chest $tile){
		parent::__construct($tile, InventoryType::get(InventoryType::CHEST));
	}
	public function getHolder(){
		return $this->holder;
	}
	public function onOpen(Player $who){
		parent::onOpen($who);

		if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
			$this->broadcastBlockEventPacket(true);
			$this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_CHEST_OPEN);
		}

		if($this->getHolder()->isValid()){
			$block = $this->getHolder()->getBlock();
			if($block instanceof TrappedChest){
				if(!$block->isActivated()){
					$block->activate();
				}
			}
		}
	}
	public function onClose(Player $who){
		if($this->getHolder()->isValid()){
			$block = $this->getHolder()->getBlock();
			if($block instanceof TrappedChest){
				if($block->isActivated()){
					$block->deactivate();
				}
			}
		}

		if(count($this->getViewers()) === 1 and $this->getHolder()->isValid()){
			$this->broadcastBlockEventPacket(false);
			$this->getHolder()->getLevel()->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_CHEST_CLOSED);
		}

		parent::onClose($who);
	}

	protected function broadcastBlockEventPacket(bool $isOpen) : void{
		$holder = $this->getHolder();

		$pk = new BlockEventPacket();
		$pk->x = (int) $holder->x;
		$pk->y = (int) $holder->y;
		$pk->z = (int) $holder->z;
		$pk->case1 = 1;
		$pk->case2 = $isOpen ? 1 : 0;
		$holder->getLevel()->broadcastPacketToViewers($holder, $pk);
	}
}