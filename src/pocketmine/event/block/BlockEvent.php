<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\Event;

abstract class BlockEvent extends Event{
	protected $block;
	public function __construct(Block $block){
		$this->block = $block;
	}
	public function getBlock(){
		return $this->block;
	}
}