<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

class AvailableCommandsPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::AVAILABLE_COMMANDS_PACKET;

	public $commands;
	public $unknown = "";
	public function decode(){
		$this->commands = $this->getString();
		$this->unknown = $this->getString();
	}
	public function encode(){
		$this->reset();
		$this->putString($this->commands);
		$this->putString($this->unknown);
	}
	public function getName(){
		return "AvailableCommandsPacket";
	}

}