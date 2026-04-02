<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

interface TransactionQueue{

	const DEFAULT_ALLOWED_RETRIES = 5;
	function getInventories();
	function getTransactions();
	function getTransactionCount();
	function addTransaction(Transaction $transaction);
	function execute();

}