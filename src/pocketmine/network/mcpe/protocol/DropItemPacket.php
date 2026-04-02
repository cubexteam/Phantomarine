<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\item\Item;

class DropItemPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::DROP_ITEM_PACKET;

	public $type;
	public $item;
	public function decode(){
		$this->type = $this->getByte();
		$this->item = $this->getSlot();
	}
	public function encode(){
		$this->reset();
		$this->putByte($this->type);
		$this->putSlot($this->item);
	}
	public function getName(){
		return "DropItemPacket";
	}

}
