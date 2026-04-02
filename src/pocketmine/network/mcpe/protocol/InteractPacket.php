<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class InteractPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::INTERACT_PACKET;

	const ACTION_RIGHT_CLICK = 1;
	const ACTION_LEFT_CLICK = 2;
	const ACTION_LEAVE_VEHICLE = 3;
	const ACTION_MOUSEOVER = 4;

	const ACTION_OPEN_INVENTORY = 6;
	public $action;
	public $target;
	public $entityRuntimeId;
	public function decode(){
		$this->action = $this->getByte();
		$this->target = $this->getEntityId();
	}
	public function encode(){
		$this->reset();
		$this->putByte($this->action);
		$this->putEntityId($this->target);
	}
	public function getName(){
		return "InteractPacket";
	}

}
