<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

class AdvertiseSystem extends Packet{
	public static $ID = MessageIdentifiers::ID_ADVERTISE_SYSTEM;
	public $serverName;

	protected function encodePayload() : void{
		$this->putString($this->serverName);
	}

	protected function decodePayload() : void{
		$this->serverName = $this->getString();
	}
}
