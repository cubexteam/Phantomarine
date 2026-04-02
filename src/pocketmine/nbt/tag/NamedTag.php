<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;


use JsonSerializable;

abstract class NamedTag extends Tag implements JsonSerializable{

	protected $__name;
	public function __construct(string $name = "", $value = null){
		$this->__name = ($name === null or $name === false) ? "" : $name;
		if($value !== null){
            $this->setValue($value);
		}
	}
	public function getName(){
		return $this->__name;
	}
	public function setName($name){
		$this->__name = $name;
	}

	public function jsonSerialize(){
		return [
			"tag" => get_class($this),
			"name" => $this->getName(),
			"value" => $this->getValue()
		];
	}
	public function equals(NamedTag $that) : bool{
		return $this->__name === $that->__name and $this->equalsValue($that);
	}
	protected function equalsValue(NamedTag $that) : bool{
		return $that instanceof $this and $this->getValue() === $that->getValue();
	}
}