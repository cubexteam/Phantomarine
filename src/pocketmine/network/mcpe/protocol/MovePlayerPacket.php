<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class MovePlayerPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::MOVE_PLAYER_PACKET;

	const MODE_NORMAL = 0;
	const MODE_RESET = 1;
	const MODE_TELEPORT = 2;
	const MODE_PITCH = 3;

	public $entityRuntimeId;
	public $x;
	public $y;
	public $z;
	public $yaw;
	public $bodyYaw;
	public $pitch;
	public $mode = self::MODE_NORMAL;
	public $onGround = false;
	public $entityRuntimeId2 = 0;
	public $teleportCause = 0;
	public $teleportItem = 0;
	public function decode(){
		$this->entityRuntimeId = $this->getEntityId();
		$this->getVector3f($this->x, $this->y, $this->z);
		$this->pitch = $this->getLFloat();
		$this->yaw = $this->getLFloat();
		$this->bodyYaw = $this->getLFloat();
		$this->mode = $this->getByte();
		$this->onGround = $this->getBool();
		$this->entityRuntimeId2 = $this->getEntityId();
		if($this->mode === MovePlayerPacket::MODE_TELEPORT){
			$this->teleportCause = $this->getLInt();
			$this->teleportItem = $this->getLInt();
		}
	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->entityRuntimeId);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putLFloat($this->bodyYaw);
		$this->putByte($this->mode);
		$this->putBool($this->onGround);
		$this->putEntityId($this->entityRuntimeId2);
		if($this->mode === MovePlayerPacket::MODE_TELEPORT){
			$this->putLInt($this->teleportCause);
			$this->putLInt($this->teleportItem);
		}
	}

}
