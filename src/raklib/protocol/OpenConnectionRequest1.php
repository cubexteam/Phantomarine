<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

use raklib\RakLib;
use function str_pad;
use function strlen;

class OpenConnectionRequest1 extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_OPEN_CONNECTION_REQUEST_1;
	public $protocol = RakLib::DEFAULT_PROTOCOL_VERSION;
	public $mtuSize;

	protected function encodePayload() : void{
		$this->writeMagic();
		$this->putByte($this->protocol);
		$this->buffer = str_pad($this->buffer, $this->mtuSize, "\x00");
	}

	protected function decodePayload() : void{
		$this->readMagic();
		$this->protocol = $this->getByte();
		$this->mtuSize = strlen($this->buffer);
	}
}
