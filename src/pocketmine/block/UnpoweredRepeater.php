<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class UnpoweredRepeater extends PoweredRepeater{
	protected $id = self::UNPOWERED_REPEATER_BLOCK;
	public function getName() : string{
		return "Unpowered Repeater";
	}
	public function getStrength(){
		return 0;
	}
	public function isActivated(Block $from = null){
		return false;
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);
		return true;
	}
}