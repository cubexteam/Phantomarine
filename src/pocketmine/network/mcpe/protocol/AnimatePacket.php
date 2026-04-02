<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class AnimatePacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::ANIMATE_PACKET;

	const ACTION_SWING_ARM = 1;

	const ACTION_STOP_SLEEP = 3;
	const ACTION_CRITICAL_HIT = 4;
	const ACTION_ROW_RIGHT = 128;
	const ACTION_ROW_LEFT = 129;
	public $action;
	public $entityRuntimeId;
	public $rowingTime = 0.0;
	public function decode(){
		$this->action = $this->getVarInt();
		$this->entityRuntimeId = $this->getEntityId();
		if(($this->action & 0x80) !== 0){
			$this->rowingTime = $this->getLFloat();
		}
	}
	public function encode(){
		$this->reset();
		$this->putVarInt($this->action);
		$this->putEntityId($this->entityRuntimeId);
		if(($this->action & 0x80) !== 0){
			$this->putLFloat($this->rowingTime);
		}
	}

}
