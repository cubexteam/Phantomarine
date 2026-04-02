<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe;

use raklib\protocol\EncapsulatedPacket;

class CachedEncapsulatedPacket extends EncapsulatedPacket{
	private $internalData = null;

	public function toInternalBinary() : string{
		return $this->internalData ?? ($this->internalData = parent::toInternalBinary());
	}
}