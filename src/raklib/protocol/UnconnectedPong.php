<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

class UnconnectedPong extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_UNCONNECTED_PONG;
	public $pingID;
	public $serverID;
	public $serverName;

	protected function encodePayload() : void{
		$this->putLong($this->pingID);
		$this->putLong($this->serverID);
		$this->writeMagic();
		$this->putString($this->serverName);
	}

	protected function decodePayload() : void{
		$this->pingID = $this->getLong();
		$this->serverID = $this->getLong();
		$this->readMagic();
		$this->serverName = $this->getString();
	}
}
