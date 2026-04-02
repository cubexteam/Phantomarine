<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

use raklib\utils\InternetAddress;

class OpenConnectionReply2 extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_OPEN_CONNECTION_REPLY_2;
	public $serverID;
	public $clientAddress;
	public $mtuSize;
	public $serverSecurity = false;

	protected function encodePayload() : void{
		$this->writeMagic();
		$this->putLong($this->serverID);
		$this->putAddress($this->clientAddress);
		$this->putShort($this->mtuSize);
		$this->putByte($this->serverSecurity ? 1 : 0);
	}

	protected function decodePayload() : void{
		$this->readMagic();
		$this->serverID = $this->getLong();
		$this->clientAddress = $this->getAddress();
		$this->mtuSize = $this->getShort();
		$this->serverSecurity = $this->getByte() !== 0;
	}
}
