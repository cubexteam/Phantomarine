<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

class ConnectedPing extends Packet{
	public static $ID = MessageIdentifiers::ID_CONNECTED_PING;
	public $sendPingTime;

	protected function encodePayload() : void{
		$this->putLong($this->sendPingTime);
	}

	protected function decodePayload() : void{
		$this->sendPingTime = $this->getLong();
	}
}
