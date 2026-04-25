<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol\types;

class DeviceTranslator {

	public static function translate(int $id) : string{
		switch($id){
			case 1:
				return "Android";
			case 2:
				return "iOS";
			case 3:
				return "macOS";
			case 4:
				return "FireOS";
			case 5:
				return "GearVR";
			case 6:
				return "HoloLens";
			case 7:
				return "Windows 10";
			case 8:
				return "Windows";
			case 9:
				return "Dedicated";
			case 10:
				return "TVOS";
			case 11:
				return "PlayStation";
			case 12:
				return "Switch";
			case 13:
				return "Xbox";
			default:
				return "Unknown";
		}
	}
}
