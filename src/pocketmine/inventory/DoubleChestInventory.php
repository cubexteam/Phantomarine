<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\tile\Chest;

class DoubleChestInventory extends ChestInventory implements InventoryHolder{
	private $left;
	private $right;
	public function __construct(Chest $left, Chest $right){
		$this->left = $left->getRealInventory();
		$this->right = $right->getRealInventory();
		$items = array_merge($this->left->getContents(true), $this->right->getContents(true));
		BaseInventory::__construct($this, InventoryType::get(InventoryType::DOUBLE_CHEST), $items);
	}
	public function getInventory(){
		return $this;
	}
	public function getHolder(){
		return $this->left->getHolder();
	}
	public function getItem($index){
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->left->getSize());
	}
	public function setItem($index, Item $item, $send = true){
		$old = $this->getItem($index);
		if($index < $this->left->getSize() ? $this->left->setItem($index, $item, $send) : $this->right->setItem($index - $this->left->getSize(), $item, $send)){
			$this->onSlotChange($index, $old, $send);
			return true;
		}
		return false;
	}

	public function getContents(bool $includeEmpty = false) : array{
		$result = $this->left->getContents($includeEmpty);
		$leftSize = $this->left->getSize();

		foreach($this->right->getContents($includeEmpty) as $i => $item){
			$result[$i + $leftSize] = $item;
		}

		return $result;
	}
	public function setContents(array $items, $send = true){
		$size = $this->getSize();
		if(count($items) > $size){
			$items = array_slice($items, 0, $size, true);
		}

		$leftSize = $this->left->getSize();

		for($i = 0; $i < $size; ++$i){
			if(!isset($items[$i])){
				if(($i < $leftSize and isset($this->left->slots[$i])) or isset($this->right->slots[$i - $leftSize])){
					$this->clear($i, false);
				}
			}elseif(!$this->setItem($i, $items[$i], false)){
				$this->clear($i, false);
			}
		}

		if($send){
			$this->sendContents($this->getViewers());
		}
	}
	public function onOpen(Player $who){
		parent::onOpen($who);

		if(count($this->getViewers()) === 1 and $this->right->getHolder()->isValid()){
			$this->right->broadcastBlockEventPacket(true);
		}
	}
	public function onClose(Player $who){
		if(count($this->getViewers()) === 1 and $this->right->getHolder()->isValid()){
			$this->right->broadcastBlockEventPacket(false);
		}
		parent::onClose($who);
	}
	public function getLeftSide(){
		return $this->left;
	}
	public function getRightSide(){
		return $this->right;
	}

	public function invalidate(){
		$this->left = null;
		$this->right = null;
	}
}