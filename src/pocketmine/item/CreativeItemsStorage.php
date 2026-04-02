<?php

namespace pocketmine\item;

final class CreativeItemsStorage{
	private static $instance;
	public static function getInstance() : CreativeItemsStorage{
		if(self::$instance === null){
			self::$instance = new CreativeItemsStorage();
		}

		return self::$instance;
	}
	private $items = [];

	private function __construct(){
	}
	public function getItems() : array{
		return $this->items;
	}
	public function clearItems(){
		$this->items = [];
	}
	public function addItem(Item $item){
		$this->items[] = clone $item;
	}
	public function removeItemByIndex(int $index){
		unset($this->items[$index]);
	}
	public function getItemByIndex(int $index) : ?Item{
		return isset($this->items[$index]) ? $this->items[$index] : null;
	}
}