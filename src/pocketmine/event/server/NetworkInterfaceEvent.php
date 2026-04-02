<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\server;

use pocketmine\network\SourceInterface;

class NetworkInterfaceEvent extends ServerEvent{
	protected $interface;
	public function __construct(SourceInterface $interface){
		$this->interface = $interface;
	}
	public function getInterface() : SourceInterface{
		return $this->interface;
	}
}