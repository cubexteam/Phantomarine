<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class GlowingRedstoneOre extends RedstoneOre implements SolidLight{

	protected $id = self::GLOWING_REDSTONE_ORE;

	protected $itemId = self::REDSTONE_ORE;
	public function getName() : string{
		return "Glowing Redstone Ore";
	}
	public function getLightLevel(){
		return 9;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		return false;
	}

	public function onNearbyBlockChange() : void{

	}
	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::REDSTONE_ORE, $this->meta), false, false);
	}
}