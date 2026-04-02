<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

class Camera extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::CAMERA, $meta, $count, "Camera");
	}
	public function getMaxStackSize() : int{
		return 1;
	}
}
