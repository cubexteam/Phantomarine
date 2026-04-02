<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class DoubleTag extends NamedTag{
    public function __construct(string $name = "", float $value = 0.0){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_Double;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = $nbt->getDouble();
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putDouble($this->value);
	}
    public function &getValue() : float{
        return parent::getValue();
    }
    public function setValue($value){
        if(!is_float($value) and !is_int($value)){
            throw new \TypeError("DoubleTag value must be of type double, " . gettype($value) . " given");
        }
        parent::setValue((float) $value);
    }
}