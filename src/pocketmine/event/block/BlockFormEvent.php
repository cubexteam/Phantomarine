<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\block;

use pocketmine\block\Block;
class BlockFormEvent extends BlockGrowEvent{
	public static $handlerList = null;

	public function __construct(Block $block, Block $newState, private Block $causingBlock){
		parent::__construct($block, $newState);
	}
	public function getCausingBlock() : Block{
		return $this->causingBlock;
	}
}