<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

class EnderPearl extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(Item::ENDER_PEARL, $meta, $count, "Ender Pearl");
	}

	public function getCooldownTicks() : int{
		return 20;
	}
	public function getMaxStackSize() : int{
		return 16;
	}
}