<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

class ShowCreditsPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::SHOW_CREDITS_PACKET;

	public $entityRuntimeId;
	public $type;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->entityRuntimeId);
		$this->putVarInt($this->type);
	}
	public function getName(){
		return "ShowCreditsPacket";
	}

}