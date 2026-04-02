<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\event\inventory\AnvilProcessEvent;
use pocketmine\item\EnchantedBook;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class AnvilInventory extends TemporaryInventory{

	const TARGET = 0;
	const SACRIFICE = 1;
	const RESULT = 2;
	public function __construct(Position $pos){
		parent::__construct(new FakeBlockMenu($this, $pos), InventoryType::get(InventoryType::ANVIL));
	}
	public function getHolder(){
		return $this->holder;
	}
	public function getResultSlotIndex(){
		return self::RESULT;
	}
	public function onRename(Player $player, Item $resultItem) : bool{
		if(!$resultItem->equals($this->getItem(self::TARGET), true, false, true)){
			return false;
		}

		if($player->getXpLevel() < $resultItem->getRepairCost()){
			return false;
		}
		$player->takeXpLevel($resultItem->getRepairCost());

		$this->clearAll();
		if(!$player->getServer()->allowInventoryCheats and !$player->isCreative()){
			if(!$player->getFloatingInventory()->canAddItem($resultItem)){
				return false;
			}
			$player->getFloatingInventory()->addItem($resultItem);
		}
		return true;
	}
	public function process(Player $player, Item $target, Item $sacrifice){
		$resultItem = clone $target;
		Server::getInstance()->getPluginManager()->callEvent($ev = new AnvilProcessEvent($this));
		if($ev->isCancelled()){
			$this->clearAll();
			return false;
		}
		if($sacrifice instanceof EnchantedBook && $sacrifice->hasEnchantments()){
			foreach($sacrifice->getEnchantments() as $enchant){
				$resultItem->addEnchantment($enchant);
			}

			if($player->getXpLevel() < $resultItem->getRepairCost()){
				return false;
			}
			$player->takeXpLevel($resultItem->getRepairCost());

			$this->clearAll();
			if(!$player->getServer()->allowInventoryCheats and !$player->isCreative()){
				if(!$player->getFloatingInventory()->canAddItem($resultItem)){
					return false;
				}
				$player->getFloatingInventory()->addItem($resultItem);
			}
		}

		return true;
	}
	public function processSlotChange(Transaction $transaction) : bool{
		if($transaction->getSlot() === $this->getResultSlotIndex()){
			return false;
		}

		return true;
	}
	public function onSlotChange($index, $before, $send){
	}
	public function onClose(Player $who){
		parent::onClose($who);

		foreach($this->getContents() as $item){
			$who->dropItem($item);
		}
		$this->clearAll();
	}
}