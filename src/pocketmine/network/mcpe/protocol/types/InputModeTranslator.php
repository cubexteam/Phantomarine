<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol\types;

class InputModeTranslator {

	public static function translate(int $id) : string{
		switch($id){
			case 1:
				return "Mouse";
			case 2:
				return "Touch";
			case 3:
				return "Gamepad";
			case 4:
				return "Motion";
			default:
				return "Unknown";
		}
	}
}
