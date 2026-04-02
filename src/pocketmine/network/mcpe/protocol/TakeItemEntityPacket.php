<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class TakeItemEntityPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::TAKE_ITEM_ENTITY_PACKET;

	public $target;
	public $entityRuntimeId;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->target);
		$this->putEntityId($this->entityRuntimeId);
	}
	public function getName(){
		return "TakeItemEntityPacket";
	}

}
