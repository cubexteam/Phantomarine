<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

class AddPlayerPacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::ADD_PLAYER_PACKET;

	public $uuid;
	public $username;
	public $entityRuntimeId;
	public $x;
	public $y;
	public $z;
	public $speedX = 0.0;
	public $speedY = 0.0;
	public $speedZ = 0.0;
	public $pitch = 0.0;
	public $headYaw = null;
	public $yaw = 0.0;
	public $item;
	public $metadata = [];
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putUUID($this->uuid);
		$this->putString($this->username);
		$this->putEntityId($this->entityRuntimeId);
		$this->putEntityId($this->entityRuntimeId);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putVector3f($this->speedX, $this->speedY, $this->speedZ);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->headYaw ?? $this->yaw);
		$this->putLFloat($this->yaw);
		$this->putSlot($this->item);
		$this->putEntityMetadata($this->metadata);
	}
	public function getName(){
		return "AddPlayerPacket";
	}

}
