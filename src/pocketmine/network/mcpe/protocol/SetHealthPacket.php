<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class SetHealthPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::SET_HEALTH_PACKET;

	public $health;
	public function decode(){
		$this->health = $this->getVarInt();
	}
	public function encode(){
		$this->reset();
		$this->putVarInt($this->health);
	}
	public function getName(){
		return "SetHealthPacket";
	}

}