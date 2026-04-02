<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

class PlaySoundPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::PLAY_SOUND_PACKET;
	public $sound;
	public $x;
	public $y;
	public $z;
	public $volume;
	public $float;
	public function decode(){
		$this->sound = $this->getString();
		$this->getBlockCoords($this->x, $this->y, $this->z);
		$this->x /= 8;
		$this->y /= 8;
		$this->z /= 8;
		$this->volume = $this->getLFloat();
		$this->float = $this->getLFloat();
	}
	public function encode(){
		$this->reset();
		$this->putString($this->sound);
		$this->putBlockCoords((int) ($this->x * 8), (int) ($this->y * 8), (int) ($this->z * 8));
		$this->putLFloat($this->volume);
		$this->putLFloat($this->float);
	}
	public function getName(){
		return "PlaySoundPacket";
	}

}
