<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\entity\Effect;

interface FoodSource{
	public function getResidue();
	public function getFoodRestore() : int;
	public function getSaturationRestore() : float;
	public function getAdditionalEffects() : array;


}
