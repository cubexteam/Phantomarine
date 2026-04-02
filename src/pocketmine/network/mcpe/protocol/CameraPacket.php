<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

#include <rules/DataPacket.h>
namespace pocketmine\network\mcpe\protocol;


class CameraPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::CAMERA_PACKET;
	public $cameraUniqueId;
	public $playerUniqueId;
	public function decode(){
		$this->cameraUniqueId = $this->getEntityId();
		$this->playerUniqueId = $this->getEntityId();
	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->cameraUniqueId);
		$this->putEntityId($this->playerUniqueId);
	}
	public function getName(){
		return "CameraPacket";
	}

}
