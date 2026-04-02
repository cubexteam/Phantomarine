<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use function serialize;
use function unserialize;
class BulkCurlTask extends AsyncTask{
	private $operations;
	public function __construct(array $operations, $complexData = null){
		$this->storeLocal($complexData);
		$this->operations = serialize($operations);
	}

	public function onRun(){
		$operations = unserialize($this->operations);
		$results = [];
		foreach($operations as $op){
			try{
				$results[] = Internet::simpleCurl($op["page"], $op["timeout"] ?? 10, $op["extraHeaders"] ?? [], $op["extraOpts"] ?? []);
			}catch(InternetException $e){
				$results[] = $e;
			}
		}
		$this->setResult($results);
	}
}