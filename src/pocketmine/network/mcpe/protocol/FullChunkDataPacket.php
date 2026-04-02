<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class FullChunkDataPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::FULL_CHUNK_DATA_PACKET;

	public $chunkX;
	public $chunkZ;
	public $data;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putVarInt($this->chunkX);
		$this->putVarInt($this->chunkZ);
		$this->putString($this->data);
	}
	public function getName(){
		return "FullChunkDataPacket";
	}

}
