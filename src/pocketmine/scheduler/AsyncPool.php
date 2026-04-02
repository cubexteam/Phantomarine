<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\Server;
use function array_keys;
use function assert;
use function count;
use function spl_object_hash;
use function time;
use const PHP_INT_MAX;
use const PTHREADS_INHERIT_CONSTANTS;
use const PTHREADS_INHERIT_INI;
class AsyncPool{
	private const WORKER_START_OPTIONS = PTHREADS_INHERIT_INI | PTHREADS_INHERIT_CONSTANTS;
	private $server;
	private $classLoader;
	private $logger;
	protected $size;
	private $workerMemoryLimit;
	private $tasks = [];
	private $taskWorkers = [];
	private $nextTaskId = 1;
	private $workers = [];
	private $workerUsage = [];
	private $workerLastUsed = [];
	private $workerStartHooks = [];

	public function __construct(Server $server, int $size, int $workerMemoryLimit, \ClassLoader $classLoader, \ThreadedLogger $logger){
		$this->server = $server;
		$this->size = (int) $size;
		$this->workerMemoryLimit = $workerMemoryLimit;
		$this->classLoader = $classLoader;
		$this->logger = $logger;
	}
	public function getSize(){
		return $this->size;
	}
	public function increaseSize($newSize) : void{
		$newSize = (int) $newSize;
		if($newSize > $this->size){
			$this->size = $newSize;
		}
	}
	public function addWorkerStartHook(\Closure $hook) : void{
		$this->workerStartHooks[spl_object_hash($hook)] = $hook;
		foreach($this->workers as $i => $worker){
			$hook($i);
		}
	}
	public function removeWorkerStartHook(\Closure $hook) : void{
		unset($this->workerStartHooks[spl_object_hash($hook)]);
	}
	public function getRunningWorkers() : array{
		return array_keys($this->workers);
	}
	private function getWorker(int $worker) : AsyncWorker{
		if(!isset($this->workers[$worker])){
			$this->workerUsage[$worker] = 0;
			$this->workers[$worker] = new AsyncWorker($this->logger, $worker, $this->workerMemoryLimit);
			$this->workers[$worker]->setClassLoader($this->classLoader);
			$this->workers[$worker]->start(self::WORKER_START_OPTIONS);

			foreach($this->workerStartHooks as $hook){
				$hook($worker);
			}
		}

		return $this->workers[$worker];
	}
	public function submitTaskToWorker(AsyncTask $task, $worker) : void{
		$worker = (int) $worker;
		if($worker < 0 or $worker >= $this->size){
			throw new \InvalidArgumentException("Invalid worker $worker");
		}
		if($task->getTaskId() !== null){
			throw new \InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$task->progressUpdates = new \Threaded;
		$taskId = $this->nextTaskId++;
		$task->setTaskId($taskId);

		$this->tasks[$taskId] = $task;

		$this->getWorker($worker)->stack($task);
		$this->workerUsage[$worker]++;
		$this->taskWorkers[$taskId] = $worker;
		$this->workerLastUsed[$worker] = time();
	}
	public function selectWorker() : int{
		$worker = null;
		$minUsage = PHP_INT_MAX;
		foreach($this->workerUsage as $i => $usage){
			if($usage < $minUsage){
				$worker = $i;
				$minUsage = $usage;
				if($usage === 0){
					break;
				}
			}
		}
		if($worker === null or ($minUsage > 0 and count($this->workers) < $this->size)){
			for($i = 0; $i < $this->size; ++$i){
				if(!isset($this->workers[$i])){
					$worker = $i;
					break;
				}
			}
		}

		assert($worker !== null);
		return $worker;
	}
	public function submitTask(AsyncTask $task) : int{
		if($task->getTaskId() !== null){
			throw new \InvalidArgumentException("Cannot submit the same AsyncTask instance more than once");
		}

		$worker = $this->selectWorker();
		$this->submitTaskToWorker($task, $worker);
		return $worker;
	}
	private function removeTask(AsyncTask $task, $force = false) : void{
		$task->setGarbage();

		if(isset($this->taskWorkers[$task->getTaskId()])){
			if(!$force and ($task->isRunning() or !$task->isGarbage())){
				return;
			}
			$this->workerUsage[$this->taskWorkers[$task->getTaskId()]]--;
			$this->workers[$this->taskWorkers[$task->getTaskId()]]->collector($task);
		}

		$task->removeDanglingStoredObjects();
		unset($this->tasks[$task->getTaskId()]);
		unset($this->taskWorkers[$task->getTaskId()]);
	}
	public function removeTasks() : void{
		foreach($this->workers as $worker){
			while(($task = $worker->unstack()) !== null){
				assert($task instanceof AsyncTask);
				$task->cancelRun();
				$this->removeTask($task, true);
			}
		}
		do{
			foreach($this->tasks as $task){
				$task->cancelRun();
				$this->removeTask($task);
			}

			if(count($this->tasks) > 0){
				Server::microSleep(25000);
			}
		}while(count($this->tasks) > 0);

		for($i = 0; $i < $this->size; ++$i){
			$this->workerUsage[$i] = 0;
		}

		$this->taskWorkers = [];
		$this->tasks = [];

		$this->collectWorkers();
	}
	private function collectWorkers() : void{
		foreach($this->workers as $worker){
			$worker->collect();
		}
	}
	public function collectTasks() : void{
		foreach($this->tasks as $task){
			$task->checkProgressUpdates($this->server);
			if($task->isGarbage() and !$task->isRunning() and !$task->isCrashed()){
				if(!$task->hasCancelledRun()){
					/*
					 * It's possible for a task to submit a progress update and then finish before the progress
					 * update is detected by the parent thread, so here we consume any missed updates.
					 *
					 * When this happens, it's possible for a progress update to arrive between the previous
					 * checkProgressUpdates() call and the next isGarbage() call, causing progress updates to be
					 * lost. Thus, it's necessary to do one last check here to make sure all progress updates have
					 * been consumed before completing.
					 */
					$task->checkProgressUpdates($this->server);
					$task->onCompletion($this->server);
				}

				$this->removeTask($task);
			}elseif($task->isCrashed()){
				$this->logger->critical("Could not execute asynchronous task " . (new \ReflectionClass($task))->getShortName() . ": Task crashed");
				$this->removeTask($task, true);
			}
		}

		$this->collectWorkers();
	}
	public function getTaskQueueSizes() : array{
		return $this->workerUsage;
	}

	public function shutdownUnusedWorkers() : int{
		$ret = 0;
		$time = time();
		foreach($this->workerUsage as $i => $usage){
			if($usage === 0 and (!isset($this->workerLastUsed[$i]) or $this->workerLastUsed[$i] + 300 < $time)){
				$this->workers[$i]->quit();
				unset($this->workers[$i], $this->workerUsage[$i], $this->workerLastUsed[$i]);
				$ret++;
			}
		}

		return $ret;
	}
	public function shutdown() : void{
		$this->collectTasks();
		$this->removeTasks();
		foreach($this->workers as $worker){
			$worker->quit();
		}
		$this->workers = [];
		$this->workerLastUsed = [];
	}
}