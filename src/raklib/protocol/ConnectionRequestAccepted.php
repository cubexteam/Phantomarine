<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace raklib\protocol;

#include <rules/RakLibPacket.h>

use raklib\RakLib;
use raklib\utils\InternetAddress;
use function strlen;

class ConnectionRequestAccepted extends Packet{
	public static $ID = MessageIdentifiers::ID_CONNECTION_REQUEST_ACCEPTED;
	public $address;
	public $systemAddresses = [];
	public $sendPingTime;
	public $sendPongTime;

	public function __construct(string $buffer = "", int $offset = 0){
		parent::__construct($buffer, $offset);
		$this->systemAddresses[] = new InternetAddress("127.0.0.1", 0, 4);
	}

	protected function encodePayload() : void{
		$this->putAddress($this->address);
		$this->putShort(0);

		$dummy = new InternetAddress("0.0.0.0", 0, 4);
		for($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i){
			$this->putAddress($this->systemAddresses[$i] ?? $dummy);
		}

		$this->putLong($this->sendPingTime);
		$this->putLong($this->sendPongTime);
	}

	protected function decodePayload() : void{
		$this->address = $this->getAddress();
		$this->getShort();

		$len = strlen($this->buffer);
		$dummy = new InternetAddress("0.0.0.0", 0, 4);

		for($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i){
			$this->systemAddresses[$i] = $this->offset + 16 < $len ? $this->getAddress() : $dummy;
		}

		$this->sendPingTime = $this->getLong();
		$this->sendPongTime = $this->getLong();
	}
}
