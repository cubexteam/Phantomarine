<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\block;

use pocketmine\block\Block;

class BlockSpreadEvent extends BlockFormEvent{
	public static $handlerList = null;
	private $source;
	public function __construct(Block $block, Block $source, Block $newState){
		parent::__construct($block, $source, $newState);
		$this->source = $source;
	}

	public function getSource() : Block{
		return $this->source;
	}
}