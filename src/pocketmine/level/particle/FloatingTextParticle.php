<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\particle;

use pocketmine\entity\Entity;
use pocketmine\entity\FallingSand;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;

class FloatingTextParticle extends Particle{

	protected $text;
	protected $title;
	protected $entityId;
	protected $invisible = false;
	public function __construct(Vector3 $pos, $text, $title = ""){
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->text = $text;
		$this->title = $title;
	}
	public function getText(){
		return $this->text;
	}

	public function getEntityId(){
		return $this->entityId;
	}
	public function getTitle(){
		return $this->title;
	}
	public function setText($text){
		$this->text = $text;
	}
	public function setTitle($title){
		$this->title = $title;
	}
	public function isInvisible(){
		return $this->invisible;
	}
	public function setInvisible($value = true){
		$this->invisible = (bool) $value;
	}
	public function encode(){
		$p = [];

		if($this->entityId === null){
			$this->entityId = Entity::$entityCount++;
		}else{
			$pk0 = new RemoveEntityPacket();
			$pk0->entityRuntimeId = $this->entityId;

			$p[] = $pk0;
		}

		if(!$this->invisible){
			$pk = new AddEntityPacket();
			$pk->entityRuntimeId = $this->entityId;
			$pk->type = FallingSand::NETWORK_ID;
			$pk->x = $this->x;
			$pk->y = $this->y - 0.49;
			$pk->z = $this->z;
			$pk->speedX = $pk->speedY = $pk->speedZ = 0.0;
			$pk->yaw = 0.0;
			$pk->pitch = 0.0;
			$pk->attributes = [];
			$flags = (
				(1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
				(1 << Entity::DATA_FLAG_IMMOBILE)
			);
			$pk->metadata = [
				Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
				Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.01],
				Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0.0],
				Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0.0],
				Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->title . ($this->text !== "" ? "\n" . $this->text : "")],
				Entity::DATA_VARIANT => [Entity::DATA_TYPE_INT, 0]
			];
			$pk->links = [];

			$p[] = $pk;
		}

		return $p;
	}
}
