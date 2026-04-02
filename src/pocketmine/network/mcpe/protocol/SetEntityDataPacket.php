<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

class SetEntityDataPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::SET_ENTITY_DATA_PACKET;

	public $entityRuntimeId;
	public $metadata;
	public function decode(){
		$this->entityRuntimeId = $this->getEntityId();
		$this->metadata = $this->getEntityMetadata(true);
	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->entityRuntimeId);
		$this->putEntityMetadata($this->metadata);
	}
	public function getName(){
		return "SetEntityDataPacket";
	}

}
