<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use ArrayAccess;
use pocketmine\nbt\NBT;
use RuntimeException;

#include <rules/NBT.h>

class CompoundTag extends NamedTag implements ArrayAccess{
    public function __construct(string $name = "", array $value = []){
		parent::__construct($name, $value);
	}
	public function getCount(){
		return count($this->getValue());
	}
	public function setValue($value){
		if(is_array($value)){
			foreach($value as $name => $tag){
				if($tag instanceof NamedTag){
                    $this->{$tag->getName()} = $tag;
                }else{
                    throw new \TypeError("CompoundTag members must be NamedTags, got " . gettype($tag) . " in given array");
				}
			}
        }else{
            throw new \TypeError("CompoundTag value must be NamedTag[], " . gettype($value) . " given");
		}
	}
	public function &getValue(){
		$result = [];
		foreach($this as $tag){
			if($tag instanceof NamedTag){
				$result[$tag->getName()] = $tag;
			}
		}

		return $result;
	}
	public function offsetExists($offset){
		return isset($this->{$offset}) and $this->{$offset} instanceof Tag;
	}
	public function offsetGet($offset){
		if(isset($this->{$offset}) and $this->{$offset} instanceof Tag){
			if($this->{$offset} instanceof ArrayAccess){
				return $this->{$offset};
			}else{
				return $this->{$offset}->getValue();
			}
		}

		return null;
	}
	public function offsetSet($offset, $value){
		if($value instanceof Tag){
			$this->{$offset} = $value;
		}elseif(isset($this->{$offset}) and $this->{$offset} instanceof Tag){
			$this->{$offset}->setValue($value);
		}
	}
	public function offsetUnset($offset){
		unset($this->{$offset});
	}
	public function getType(){
		return NBT::TAG_Compound;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = [];
		do{
			$tag = $nbt->readTag($network);
			if($tag instanceof NamedTag and $tag->__name !== ""){
				$this->{$tag->__name} = $tag;
			}
		}while(!($tag instanceof EndTag) && !$nbt->feof());
	}
	public function setTag(NamedTag $tag, bool $force = false) : void{
		if(!$force){
			$existing = $this->value[$tag->__name] ?? null;
			if($existing !== null and !($tag instanceof $existing)){
				throw new RuntimeException("Cannot set tag at \"$tag->__name\": tried to overwrite " . get_class($existing) . " with " . get_class($tag));
			}
		}
		$this->value[$tag->__name] = $tag;
	}
	public function write(NBT $nbt, bool $network = false){
		foreach($this as $tag){
			if($tag instanceof Tag and !($tag instanceof EndTag)){
				$nbt->writeTag($tag, $network);
			}
		}

		$nbt->writeTag(new EndTag, $network);
	}
	public function __toString(){
		$str = get_class($this) . "{\n";
		foreach($this as $tag){
			if($tag instanceof Tag){
				$str .= get_class($tag) . ":" . $tag->__toString() . "\n";
			}
		}
		return $str . "}";
	}

	public function jsonSerialize(){
		$result = [
			"tag" => get_class($this),
			"name" => $this->getName(),
			"value" => []
		];

		foreach($this as $tag){
			if($this instanceof Tag){
				$result["value"][] = $tag;
			}
		}

		return $result;
	}

	public function __clone(){
		foreach($this as $key => $tag){
			if($tag instanceof Tag){
				$this->{$key} = clone $tag;
			}
		}
	}

	public function getBoolean(string $name) : bool{
		return isset($this->{$name}) && $this->{$name} instanceof ByteTag && $this->{$name}->value;
	}

	public function getByte(string $name) : ?int{
		return isset($this->{$name}) && $this->{$name} instanceof ByteTag ? $this->{$name}->value : null;
	}

	public function getInt(string $name) : ?int{
		return isset($this->{$name}) && $this->{$name} instanceof IntTag ? $this->{$name}->value : null;
	}

	public function getFloat(string $name) : ?float{
		return isset($this->{$name}) && $this->{$name} instanceof FloatTag ? $this->{$name}->value : null;
	}

	public function putBoolean(string $name, bool $value) : void{
		$this->{$name} = new ByteTag($name, $value ? 1 : 0);
	}

	public function putByte(string $name, int $value) : void{
		$this->{$name} = new ByteTag($name, $value);
	}

	public function putInt(string $name, int $value) : void{
		$this->{$name} = new IntTag($name, $value);
	}

	public function putFloat(string $name, float $value) : void{
		$this->{$name} = new FloatTag($name, $value);
	}
}