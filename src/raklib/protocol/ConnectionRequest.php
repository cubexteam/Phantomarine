<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

class ConnectionRequest extends Packet{
	public static $ID = MessageIdentifiers::ID_CONNECTION_REQUEST;
	public $clientID;
	public $sendPingTime;
	public $useSecurity = false;

	protected function encodePayload() : void{
		$this->putLong($this->clientID);
		$this->putLong($this->sendPingTime);
		$this->putByte($this->useSecurity ? 1 : 0);
	}

	protected function decodePayload() : void{
		$this->clientID = $this->getLong();
		$this->sendPingTime = $this->getLong();
		$this->useSecurity = $this->getByte() !== 0;
	}
}
