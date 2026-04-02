<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\permission;


interface ServerOperator{
	public function isOp();
	public function setOp($value);
}