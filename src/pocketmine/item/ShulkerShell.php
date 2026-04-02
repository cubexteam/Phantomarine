<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;


class ShulkerShell extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::SHULKER_SHELL, $meta, $count, "Shulker Shell");
	}

}