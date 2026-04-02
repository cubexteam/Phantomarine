<?php

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
interface Feedable{
	public function canFeed(ItemItem $item) : bool;
	public function feed(ItemItem $item) : void;
}