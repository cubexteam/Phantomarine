<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class IntTag extends NamedTag{
    public function __construct(string $name = "", int $value = 0){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_Int;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = $nbt->getInt($network);
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putInt($this->value, $network);
	}
    public function &getValue() : int{
        return parent::getValue();
    }
    public function setValue($value){
        if(!is_int($value)){
            throw new \TypeError("IntTag value must be of type int, " . gettype($value) . " given");
        }elseif($value < -(2 ** 31) or $value > ((2 ** 31) - 1)){
            throw new \InvalidArgumentException("Value $value is too large!");
        }
        parent::setValue($value);
    }
}