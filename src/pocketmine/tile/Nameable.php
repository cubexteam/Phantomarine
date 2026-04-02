<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\tile;


interface Nameable{
	public function getName() : string;
	public function setName(string $str);
	public function hasName() : bool;
}
