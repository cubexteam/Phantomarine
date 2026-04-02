<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;


class ChunkRadiusUpdatedPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::CHUNK_RADIUS_UPDATED_PACKET;

	public $radius;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putVarInt($this->radius);
	}
	public function getName(){
		return "ChunkRadiusUpdatedPacket";
	}

}