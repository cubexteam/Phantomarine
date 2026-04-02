<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

class RedSandstoneStairs extends SandstoneStairs{

	protected $id = Block::RED_SANDSTONE_STAIRS;
	public function getName() : string{
		return "Red Sandstone Stairs";
	}
}