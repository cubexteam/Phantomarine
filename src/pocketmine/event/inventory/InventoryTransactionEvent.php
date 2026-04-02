<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\inventory\TransactionQueue;
class InventoryTransactionEvent extends Event implements Cancellable{

	public static $handlerList = null;
	private $transactionQueue;
	public function __construct(TransactionQueue $transactionQueue){
		$this->transactionQueue = $transactionQueue;
	}
	public function getTransaction(){
		return $this->transactionQueue;
	}
	public function getQueue(){
		return $this->transactionQueue;
	}
}