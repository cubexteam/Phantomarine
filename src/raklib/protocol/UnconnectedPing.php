<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

class UnconnectedPing extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_UNCONNECTED_PING;
	public $pingID;

	protected function encodePayload() : void{
		$this->putLong($this->pingID);
		$this->writeMagic();
	}

	protected function decodePayload() : void{
		$this->pingID = $this->getLong();
		$this->readMagic();
	}
}
