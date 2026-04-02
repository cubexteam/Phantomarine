<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;
class InventoryType{

	const CHEST = 0;
	const DOUBLE_CHEST = 1;
	const PLAYER = 2;
	const FURNACE = 3;
	const CRAFTING = 4;
	const WORKBENCH = 5;
	const BREWING_STAND = 7;
	const ANVIL = 8;
	const ENCHANT_TABLE = 9;
	const DISPENSER = 10;
	const DROPPER = 11;
	const HOPPER = 12;
	const ENDER_CHEST = 13;
	const BEACON = 14;
	const SHULKER_BOX = 15;

	const PLAYER_FLOATING = 254;

	private static $default = [];

	private $size;
	private $title;
	private $typeId;
	public static function get($index){
		return isset(static::$default[$index]) ? static::$default[$index] : null;
	}

	public static function init(){
		if(count(static::$default) > 0){
			return;
		}

		static::$default = [
			static::CHEST => new InventoryType(27, "Chest", WindowTypes::CONTAINER),
			static::DOUBLE_CHEST => new InventoryType(27 + 27, "Double Chest", WindowTypes::CONTAINER),
			static::PLAYER => new InventoryType(36 + 4, "Player", WindowTypes::INVENTORY),
			static::CRAFTING => new InventoryType(5, "Crafting", WindowTypes::INVENTORY),
			static::WORKBENCH => new InventoryType(10, "Crafting", WindowTypes::WORKBENCH),
			static::FURNACE => new InventoryType(3, "Furnace", WindowTypes::FURNACE),
			static::ENCHANT_TABLE => new InventoryType(2, "Enchant", WindowTypes::ENCHANTMENT),
			static::BREWING_STAND => new InventoryType(4, "Brewing", WindowTypes::BREWING_STAND),
			static::ANVIL => new InventoryType(3, "Anvil", WindowTypes::ANVIL),
			static::DISPENSER => new InventoryType(9, "Dispenser", WindowTypes::DISPENSER),
			static::DROPPER => new InventoryType(9, "Dropper", WindowTypes::DROPPER),
			static::HOPPER => new InventoryType(5, "Hopper", WindowTypes::HOPPER),
			static::ENDER_CHEST => new InventoryType(27, "Ender Chest", WindowTypes::CONTAINER),
			static::BEACON => new InventoryType(0, "Beacon", WindowTypes::BEACON),
			static::SHULKER_BOX => new InventoryType(27, "ShulkerBox", WindowTypes::CONTAINER),

			static::PLAYER_FLOATING => new InventoryType(36, "Floating", null)
		];
	}
	private function __construct($defaultSize, $defaultTitle, $typeId = 0){
		$this->size = $defaultSize;
		$this->title = $defaultTitle;
		$this->typeId = $typeId;
	}
	public function getDefaultSize(){
		return $this->size;
	}
	public function getDefaultTitle(){
		return $this->title;
	}
	public function getNetworkType(){
		return $this->typeId;
	}
}