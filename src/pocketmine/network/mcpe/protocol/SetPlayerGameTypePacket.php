<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class SetPlayerGameTypePacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::SET_PLAYER_GAME_TYPE_PACKET;

	public $gamemode;
	public function decode(){
		$this->gamemode = $this->getVarInt();
	}
	public function encode(){
		$this->reset();
		$this->putVarInt($this->gamemode);
	}
	public function getName(){
		return "SetPlayerGameTypePacket";
	}

}