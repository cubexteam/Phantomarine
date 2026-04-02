<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\Collectable;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use function is_scalar;
use function is_string;
use function serialize;
use function unserialize;
abstract class AsyncTask extends Collectable{
	private static $threadLocalStorage;
	public $worker = null;
	public $progressUpdates;
	private $result = null;
	private $serialized = false;
	private $cancelRun = false;
	private $taskId = null;
	private $crashed = false;

	private $isGarbage = false;
	public function isGarbage() : bool{
		return $this->isGarbage;
	}

	public function setGarbage(){
		$this->isGarbage = true;
	}
	public function run(){
		$this->result = null;
		$this->isGarbage = false;

		if(!$this->cancelRun){
			try{
				$this->onRun();
			}catch(\Throwable $e){
				$this->crashed = true;
				$this->worker->handleException($e);
			}
		}

		$this->setGarbage();
	}
	public function isCrashed(){
		return $this->crashed or $this->isTerminated();
	}
	public function getResult(){
		if($this->serialized){
			if(!is_string($this->result)) throw new AssumptionFailedError("Result expected to be a serialized string");
			return unserialize($this->result);
		}
		return $this->result;
	}
	public function cancelRun(){
		$this->cancelRun = true;
	}

	public function hasCancelledRun() : bool{
		return $this->cancelRun === true;
	}

	public function hasResult() : bool{
		return $this->result !== null;
	}
	public function setResult($result){
		$this->result = ($this->serialized = !is_scalar($result)) ? serialize($result) : $result;
	}
	public function setTaskId(int $taskId){
		$this->taskId = $taskId;
	}
	public function getTaskId(){
		return $this->taskId;
	}
	public function getFromThreadStore(string $identifier){
		if($this->worker === null or $this->isGarbage()){
			throw new \BadMethodCallException("Objects stored in AsyncWorker thread-local storage can only be retrieved during task execution");
		}
		return $this->worker->getFromThreadStore($identifier);
	}
	public function saveToThreadStore(string $identifier, $value){
		if($this->worker === null or $this->isGarbage()){
			throw new \BadMethodCallException("Objects can only be added to AsyncWorker thread-local storage during task execution");
		}
		$this->worker->saveToThreadStore($identifier, $value);
	}
	public function removeFromThreadStore(string $identifier) : void{
		if($this->worker === null or $this->isGarbage()){
			throw new \BadMethodCallException("Objects can only be removed from AsyncWorker thread-local storage during task execution");
		}
		$this->worker->removeFromThreadStore($identifier);
	}
	public abstract function onRun();
	public function onCompletion(Server $server){

	}
	public function publishProgress($progress){
		$this->progressUpdates[] = serialize($progress);
	}
	public function checkProgressUpdates(Server $server){
		while($this->progressUpdates->count() !== 0){
			$progress = $this->progressUpdates->shift();
			$this->onProgressUpdate($server, unserialize($progress));
		}
	}
	public function onProgressUpdate(Server $server, $progress){

	}
	protected function storeLocal($complexData){
		if($this->worker !== null and $this->worker === \Thread::getCurrentThread()){
			throw new \BadMethodCallException("Objects can only be stored from the parent thread");
		}

		if(self::$threadLocalStorage === null){
			self::$threadLocalStorage = new \SplObjectStorage();
		}

		if(isset(self::$threadLocalStorage[$this])){
			throw new \InvalidStateException("Already storing complex data for this async task");
		}
		self::$threadLocalStorage[$this] = $complexData;
	}
	protected function fetchLocal(){
		if($this->worker !== null and $this->worker === \Thread::getCurrentThread()){
			throw new \BadMethodCallException("Objects can only be retrieved from the parent thread");
		}

		if(self::$threadLocalStorage === null or !isset(self::$threadLocalStorage[$this])){
			throw new \InvalidStateException("No complex data stored for this async task");
		}

		return self::$threadLocalStorage[$this];
	}
	protected function peekLocal(){
		return $this->fetchLocal();
	}
	public function removeDanglingStoredObjects() : void{
		if(self::$threadLocalStorage !== null and isset(self::$threadLocalStorage[$this])){
			unset(self::$threadLocalStorage[$this]);
		}
	}
}