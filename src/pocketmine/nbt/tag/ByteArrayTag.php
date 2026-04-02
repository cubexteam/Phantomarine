<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class ByteArrayTag extends NamedTag{
    public function __construct(string $name = "", string $value = ""){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_ByteArray;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = $nbt->get($nbt->getInt($network));
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putInt(strlen($this->value), $network);
		$nbt->put($this->value);
	}
    public function &getValue() : string{
        return parent::getValue();
    }
    public function setValue($value){
        if(!is_string($value)){
            throw new \TypeError("ByteArrayTag value must be of type string, " . gettype($value) . " given");
        }
        parent::setValue($value);
    }
}