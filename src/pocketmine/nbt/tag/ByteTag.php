<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class ByteTag extends NamedTag{
    public function __construct(string $name = "", ?int $value = 0){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_Byte;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = $nbt->getSignedByte();
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putByte($this->value);
	}
    public function &getValue() : int{
        return parent::getValue();
    }
    public function setValue($value){
        if(!is_int($value)){
            throw new \TypeError("ByteTag value must be of type int, " . gettype($value) . " given");
        }elseif($value < -(2 ** 7) or $value > ((2 ** 7) - 1)){
            throw new \InvalidArgumentException("Value $value is too large!");
        }
        parent::setValue($value);
    }
}