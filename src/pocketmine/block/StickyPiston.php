<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

class StickyPiston extends Piston{

	protected $id = self::STICKY_PISTON;

	public $meta = 0;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}
}
