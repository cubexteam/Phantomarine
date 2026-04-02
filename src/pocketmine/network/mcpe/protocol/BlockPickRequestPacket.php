<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class BlockPickRequestPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::BLOCK_PICK_REQUEST_PACKET;

	public $tileX;
	public $tileY;
	public $tileZ;
	public $hotbarSlot;

	public function decode(){
		$this->getBlockCoords($this->tileX, $this->tileY, $this->tileZ);
		$this->hotbarSlot = $this->getByte();
	}

	public function encode(){
		$this->reset();
		$this->putBlockCoords($this->tileX, $this->tileY, $this->tileZ);
		$this->putByte($this->hotbarSlot);
	}
	public function getName(){
		return "BlockPickRequestPacket";
	}
}