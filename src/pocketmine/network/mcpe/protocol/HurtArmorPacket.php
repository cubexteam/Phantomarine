<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class HurtArmorPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::HURT_ARMOR_PACKET;

	public $health;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putVarInt($this->health);
	}
	public function getName(){
		return "HurtArmorPacket";
	}

}