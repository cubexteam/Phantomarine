<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class StringTag extends NamedTag{
    public function __construct(string $name = "", string $value = ""){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_String;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = $nbt->getString($network);
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putString($this->value, $network);
	}
    public function &getValue() : string{
        return parent::getValue();
    }
    public function setValue($value){
        if(!is_string($value)){
            throw new \TypeError("ShortTag value must be of type int, " . gettype($value) . " given");
        }
        parent::setValue($value);
    }
}