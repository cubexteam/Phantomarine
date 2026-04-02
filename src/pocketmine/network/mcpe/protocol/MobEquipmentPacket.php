<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class MobEquipmentPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::MOB_EQUIPMENT_PACKET;

	public $entityRuntimeId;
	public $item;
	public $inventorySlot;
	public $hotbarSlot;
	public $windowId = 0;
	public function decode(){
		$this->entityRuntimeId = $this->getEntityId();
		$this->item = $this->getSlot();
		$this->inventorySlot = $this->getByte();
		$this->hotbarSlot = $this->getByte();
		$this->windowId = $this->getByte();
	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->entityRuntimeId);
		$this->putSlot($this->item);
		$this->putByte($this->inventorySlot);
		$this->putByte($this->hotbarSlot);
		$this->putByte($this->windowId);
	}

}
