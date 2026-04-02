<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

class ChangeDimensionPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::CHANGE_DIMENSION_PACKET;
	public $dimension;
	public $x;
	public $y;
	public $z;
	public $respawn = false;

	public function decode(){
		$this->dimension = $this->getVarInt();
		$this->getVector3f($this->x, $this->y, $this->z);
		$this->respawn = $this->getBool();
	}

	public function encode(){
		$this->reset();
		$this->putVarInt($this->dimension);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putBool($this->respawn);
	}

}