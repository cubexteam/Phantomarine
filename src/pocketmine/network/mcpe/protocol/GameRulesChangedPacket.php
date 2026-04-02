<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

class GameRulesChangedPacket extends DataPacket{
	const NETWORK_ID = ProtocolInfo::GAME_RULES_CHANGED_PACKET;

	public $gameRules = [];

	public function decode(){
		$this->gameRules = $this->getGameRules();
	}

	public function encode(){
		$this->reset();
		$this->putGameRules($this->gameRules);
	}
}