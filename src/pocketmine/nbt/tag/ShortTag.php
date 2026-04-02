<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class ShortTag extends NamedTag{
    public function __construct(string $name = "", int $value = 0){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_Short;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = $nbt->getSignedShort();
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putShort($this->value);
	}
    public function &getValue() : int{
        return parent::getValue();
    }
    public function setValue($value){
        if(!is_int($value)){
            throw new \TypeError("ShortTag value must be of type int, " . gettype($value) . " given");
        }elseif($value < -(2 ** 15) or $value > ((2 ** 15) - 1)){
            throw new \InvalidArgumentException("Value $value is too large!");
        }
        parent::setValue($value);
    }
}