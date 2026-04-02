<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class IntArrayTag extends NamedTag{
    public function __construct(string $name = "", array $value = []){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_IntArray;
	}
	public function read(NBT $nbt, bool $network = false){
		$size = $nbt->getInt($network);
		$this->value = array_values(unpack($nbt->endianness === NBT::LITTLE_ENDIAN ? "V*" : "N*", $nbt->get($size * 4)));
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putInt(count($this->value), $network);
		$nbt->put(pack($nbt->endianness === NBT::LITTLE_ENDIAN ? "V*" : "N*", ...$this->value));
	}
    public function &getValue() : array{
        return parent::getValue();
    }
    public function setValue($value){
        if(!is_array($value)){
            throw new \TypeError("IntArrayTag value must be of type int[], " . gettype($value) . " given");
        }
        assert(count(array_filter($value, function($v){
                return !is_int($v);
            })) === 0);

        parent::setValue($value);
    }
	public function __toString(){
		$str = get_class($this) . "{\n";
		$str .= implode(", ", $this->value);
		return $str . "}";
	}
}