<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;

abstract class DataPacket extends BinaryStream{

	const NETWORK_ID = 0;

	public $isEncoded = false;
	public function pid(){
		return $this::NETWORK_ID;
	}

	public function canBeBatched() : bool{
		return true;
	}

	public function canBeSentBeforeLogin() : bool{
		return true;
	}
	public function mayHaveUnreadBytes() : bool{
		return false;
	}
	abstract public function encode();
	abstract public function decode();

	public function reset(){
		$this->buffer = chr($this::NETWORK_ID);
		$this->offset = 0;
	}
	public function clean(){
		$this->buffer = null;
		$this->isEncoded = false;
		$this->offset = 0;

		return $this;
	}
	public function __debugInfo(){
		$data = [];
		foreach((array) $this as $k => $v){
			if($k === "buffer" and is_string($v)){
				$data[$k] = bin2hex($v);
			}elseif(is_string($v) or (is_object($v) and method_exists($v, "__toString"))){
				$data[$k] = Utils::printable((string) $v);
			}else{
				$data[$k] = $v;
			}
		}

		return $data;
	}
	public function getEntityMetadata(bool $types = true) : array{
		$count = $this->getUnsignedVarInt();
		$data = [];
		for($i = 0; $i < $count && !$this->feof(); ++$i){
			$key = $this->getUnsignedVarInt();
			$type = $this->getUnsignedVarInt();
			$value = null;
			switch($type){
				case Entity::DATA_TYPE_BYTE:
					$value = $this->getByte();
					break;
				case Entity::DATA_TYPE_SHORT:
					$value = $this->getSignedLShort();
					break;
				case Entity::DATA_TYPE_INT:
					$value = $this->getVarInt();
					break;
				case Entity::DATA_TYPE_FLOAT:
					$value = $this->getLFloat();
					break;
				case Entity::DATA_TYPE_STRING:
					$value = $this->getString();
					break;
				case Entity::DATA_TYPE_SLOT:
					$value = [];
					$item = $this->getSlot();
					$value[0] = $item->getId();
					$value[1] = $item->getCount();
					$value[2] = $item->getDamage();
					break;
				case Entity::DATA_TYPE_POS:
					$value = [];
					$value[0] = $this->getVarInt();
					$value[1] = $this->getVarInt();
					$value[2] = $this->getVarInt();
					break;
				case Entity::DATA_TYPE_LONG:
					$value = $this->getVarLong();
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					$value = [0.0, 0.0, 0.0];
					$this->getVector3f($value[0], $value[1], $value[2]);
					break;
				default:
					$value = [];
			}
			if($types === true){
				$data[$key] = [$type, $value];
			}else{
				$data[$key] = $value;
			}
		}

		return $data;
	}
	public function putEntityMetadata(array $metadata){
		$this->putUnsignedVarInt(count($metadata));
		foreach($metadata as $key => $d){
			$this->putUnsignedVarInt($key);
			$this->putUnsignedVarInt($d[0]);
			switch($d[0]){
				case Entity::DATA_TYPE_BYTE:
					$this->putByte($d[1]);
					break;
				case Entity::DATA_TYPE_SHORT:
					$this->putLShort($d[1]);
					break;
				case Entity::DATA_TYPE_INT:
					$this->putVarInt($d[1]);
					break;
				case Entity::DATA_TYPE_FLOAT:
					$this->putLFloat($d[1]);
					break;
				case Entity::DATA_TYPE_STRING:
					$this->putString($d[1]);
					break;
				case Entity::DATA_TYPE_SLOT:
					$this->putSlot(Item::get($d[1][0], $d[1][2], $d[1][1]));
					break;
				case Entity::DATA_TYPE_POS:
					$this->putVarInt($d[1][0]);
					$this->putVarInt($d[1][1]);
					$this->putVarInt($d[1][2]);
					break;
				case Entity::DATA_TYPE_LONG:
					$this->putVarLong($d[1]);
					break;
				case Entity::DATA_TYPE_VECTOR3F:
					$this->putVector3f($d[1][0], $d[1][1], $d[1][2]);
			}
		}
	}

	public function getByteRotation() : float{
		return (float) ($this->getByte() * (360 / 256));
	}

	public function putByteRotation(float $rotation){
		$this->putByte((int) ($rotation / (360 / 256)));
	}
	public function getGameRules() : array{
		$count = $this->getUnsignedVarInt();
		$rules = [];
		for($i = 0; $i < $count; ++$i){
			$name = $this->getString();
			$type = $this->getUnsignedVarInt();
			$value = null;
			switch($type){
				case 1:
					$value = $this->getBool();
					break;
				case 2:
					$value = $this->getUnsignedVarInt();
					break;
				case 3:
					$value = $this->getLFloat();
					break;
			}

			$rules[$name] = [$type, $value];
		}

		return $rules;
	}
	public function putGameRules(array $rules){
		$this->putUnsignedVarInt(count($rules));
		foreach($rules as $name => $rule){
			$this->putString($name);
			$this->putUnsignedVarInt($rule[0]);
			switch($rule[0]){
				case 1:
					$this->putBool($rule[1]);
					break;
				case 2:
					$this->putUnsignedVarInt($rule[1]);
					break;
				case 3:
					$this->putLFloat($rule[1]);
					break;
			}
		}
	}
	public function getName(){
		return "DataPacket";
	}
}