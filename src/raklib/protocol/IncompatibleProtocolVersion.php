<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

class IncompatibleProtocolVersion extends OfflineMessage{
	public static $ID = MessageIdentifiers::ID_INCOMPATIBLE_PROTOCOL_VERSION;
	public $protocolVersion;
	public $serverId;

	protected function encodePayload() : void{
		$this->putByte($this->protocolVersion);
		$this->writeMagic();
		$this->putLong($this->serverId);
	}

	protected function decodePayload() : void{
		$this->protocolVersion = $this->getByte();
		$this->readMagic();
		$this->serverId = $this->getLong();
	}
}
