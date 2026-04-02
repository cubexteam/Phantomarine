<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class SetEntityMotionPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::SET_ENTITY_MOTION_PACKET;

	public $entityRuntimeId;
	public $motionX;
	public $motionY;
	public $motionZ;
	public function clean(){
		return parent::clean();
	}
	public function decode(){
		$this->entityRuntimeId = $this->getEntityId();
		$this->getVector3f($this->motionX, $this->motionY, $this->motionZ);
	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->entityRuntimeId);
		$this->putVector3f($this->motionX, $this->motionY, $this->motionZ);
	}
	public function getName(){
		return "SetEntityMotionPacket";
	}

}
