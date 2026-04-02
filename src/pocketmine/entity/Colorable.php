<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

interface Colorable{

	public function getColor() : int;

	public function setColor(int $color) : void;
}