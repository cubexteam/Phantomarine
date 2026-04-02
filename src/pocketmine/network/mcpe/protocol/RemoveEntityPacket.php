<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class RemoveEntityPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::REMOVE_ENTITY_PACKET;

	public $entityRuntimeId;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->entityRuntimeId);
	}

}
