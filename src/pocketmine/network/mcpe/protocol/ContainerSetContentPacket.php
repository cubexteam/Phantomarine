<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\ContainerIds;

class ContainerSetContentPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::CONTAINER_SET_CONTENT_PACKET;

	public $windowid;
	public $targetEid;
	public $slots = [];
	public $hotbar = [];
	public function clean(){
		$this->slots = [];
		$this->hotbar = [];

		return parent::clean();
	}
	public function decode(){
		$this->windowid = $this->getUnsignedVarInt();
		$this->targetEid = $this->getEntityId();
		$count = $this->getUnsignedVarInt();
		for($s = 0; $s < $count and !$this->feof(); ++$s){
			$this->slots[$s] = $this->getSlot();
		}

		$hotbarCount = $this->getUnsignedVarInt();
		for($s = 0; $s < $hotbarCount and !$this->feof(); ++$s){
			$this->hotbar[$s] = $this->getVarInt();
		}
	}
	public function encode(){
		$this->reset();
		$this->putUnsignedVarInt($this->windowid);
		$this->putEntityId($this->targetEid);
		$this->putUnsignedVarInt(count($this->slots));
		foreach($this->slots as $slot){
			$this->putSlot($slot);
		}
        if($this->windowid === ContainerIds::INVENTORY and count($this->hotbar) > 0){
			$this->putUnsignedVarInt(count($this->hotbar));
			foreach($this->hotbar as $slot){
				$this->putVarInt($slot);
			}
		}else{
			$this->putUnsignedVarInt(0);
		}
	}
	public function getName(){
		return "ContainerSetContentPacket";
	}

}