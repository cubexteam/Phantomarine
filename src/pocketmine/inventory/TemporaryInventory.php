<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\Player;

abstract class TemporaryInventory extends ContainerInventory{
	abstract public function getResultSlotIndex();
	public function onClose(Player $who){
		foreach($this->getContents() as $slot => $item){
			if($slot === $this->getResultSlotIndex()){
				continue;
			}
			$who->dropItem($item);
		}
		$this->clearAll();
	}
}