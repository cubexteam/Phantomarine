<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\nbt\tag;

use pocketmine\nbt\NBT;

#include <rules/NBT.h>

class LongTag extends NamedTag{
    public function __construct(string $name = "", int $value = 0){
        parent::__construct($name, $value);
    }
	public function getType(){
		return NBT::TAG_Long;
	}
	public function read(NBT $nbt, bool $network = false){
		$this->value = $nbt->getLong($network);
	}
	public function write(NBT $nbt, bool $network = false){
		$nbt->putLong($this->value, $network);
	}
	public function &getValue() : int{
		return parent::getValue();
	}
	public function setValue($value) : void{
		if(!is_int($value)){
			throw new \TypeError("LongTag value must be of type int, " . gettype($value) . " given");
		}
		parent::setValue($value);
	}
}