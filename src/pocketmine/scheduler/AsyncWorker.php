<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\utils\MainLogger;
use pocketmine\utils\Utils;
use pocketmine\Worker;
use function error_reporting;
use function gc_enable;
use function ini_set;
use function set_error_handler;

class AsyncWorker extends Worker{
	private static $store = [];
	private $logger;
	private $id;
	private $memoryLimit;

	public function __construct(\ThreadedLogger $logger, int $id, int $memoryLimit){
		$this->logger = $logger;
		$this->id = $id;
		$this->memoryLimit = $memoryLimit;
	}
	public function run(){
		error_reporting(-1);

		$this->registerClassLoader();

		set_error_handler([Utils::class, 'errorExceptionHandler']);

		if($this->logger instanceof MainLogger){
			$this->logger->registerStatic();
		}

		gc_enable();

		if($this->memoryLimit > 0){
			ini_set('memory_limit', $this->memoryLimit . 'M');
			$this->logger->debug("Set memory limit to " . $this->memoryLimit . " MB");
		}else{
			ini_set('memory_limit', '-1');
			$this->logger->debug("No memory limit set");
		}
	}

	public function getLogger() : \ThreadedLogger{
		return $this->logger;
	}
	public function handleException(\Throwable $e){
		$this->logger->logException($e);
	}
	public function getThreadName(){
		return "Asynchronous Worker #" . $this->id;
	}

	public function getAsyncWorkerId() : int{
		return $this->id;
	}
	public function saveToThreadStore(string $identifier, $value) : void{
		self::$store[$identifier] = $value;
	}
	public function getFromThreadStore(string $identifier){
		return self::$store[$identifier] ?? null;
	}
	public function removeFromThreadStore(string $identifier) : void{
		unset(self::$store[$identifier]);
	}
}