<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class ClientToServerHandshakePacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::CLIENT_TO_SERVER_HANDSHAKE_PACKET;
	public function canBeSentBeforeLogin() : bool{
		return true;
	}
	public function decode(){
	}
	public function encode(){
		$this->reset();
	}
}
