<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class ServerToClientHandshakePacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::SERVER_TO_CLIENT_HANDSHAKE_PACKET;

	public $publicKey;
	public $serverToken;
	public function canBeSentBeforeLogin() : bool{
		return true;
	}
	public function decode(){
		$this->publicKey = $this->getString();
		$this->serverToken = $this->getString();
	}
	public function encode(){
		$this->reset();
		$this->putString($this->publicKey);
		$this->putString($this->serverToken);
	}
}