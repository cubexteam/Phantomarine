<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

class ItemFrameDropItemPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::ITEM_FRAME_DROP_ITEM_PACKET;

	public $x;
	public $y;
	public $z;
	public function decode(){
		$this->getBlockCoords($this->x, $this->y, $this->z);
	}
	public function encode(){

	}
	public function getName(){
		return "ItemFrameDropItemPacket";
	}

}