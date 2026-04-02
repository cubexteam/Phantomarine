<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class ContainerSetDataPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::CONTAINER_SET_DATA_PACKET;

	public $windowid;
	public $property;
	public $value;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putByte($this->windowid);
		$this->putVarInt($this->property);
		$this->putVarInt($this->value);
	}
	public function getName(){
		return "ContainerSetDataPacket";
	}

}