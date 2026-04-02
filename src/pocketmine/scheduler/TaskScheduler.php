<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\utils\ReversePriorityQueue;

class TaskScheduler{
	private $owner;
	private $enabled = true;
	protected $queue;
	protected $tasks = [];
	private $ids = 1;
	protected $currentTick = 0;
	public function __construct(\Logger $logger, ?string $owner = null){
		$this->owner = $owner;
		$this->queue = new ReversePriorityQueue();
	}
	public function scheduleTask(Task $task){
		return $this->addTask($task, -1, -1);
	}
	public function scheduleDelayedTask(Task $task, $delay){
		return $this->addTask($task, (int) $delay, -1);
	}
	public function scheduleRepeatingTask(Task $task, $period){
		return $this->addTask($task, -1, (int) $period);
	}
	public function scheduleDelayedRepeatingTask(Task $task, $delay, $period){
		return $this->addTask($task, (int) $delay, (int) $period);
	}
	public function cancelTask($taskId){
		if(isset($this->tasks[$taskId])){
			try{
				$this->tasks[$taskId]->cancel();
			}finally{
				unset($this->tasks[$taskId]);
			}
		}
	}

	public function cancelAllTasks(){
		foreach($this->tasks as $id => $task){
			$this->cancelTask($id);
		}
		$this->tasks = [];
		while(!$this->queue->isEmpty()){
			$this->queue->extract();
		}
		$this->ids = 1;
	}
	public function isQueued($taskId){
		return isset($this->tasks[$taskId]);
	}
	private function addTask(Task $task, $delay, $period){
		if(!$this->enabled){
			throw new \InvalidStateException("Tried to schedule task to disabled scheduler");
		}

		if($delay <= 0){
			$delay = -1;
		}

		if($period <= -1){
			$period = -1;
		}elseif($period < 1){
			$period = 1;
		}

		return $this->handle(new TaskHandler($task, $this->nextId(), $delay, $period, $this->owner));
	}

	private function handle(TaskHandler $handler) : TaskHandler{
		if($handler->isDelayed()){
			$nextRun = $this->currentTick + $handler->getDelay();
		}else{
			$nextRun = $this->currentTick;
		}

		$handler->setNextRun($nextRun);
		$this->tasks[$handler->getTaskId()] = $handler;
		$this->queue->insert($handler, $nextRun);

		return $handler;
	}

	public function shutdown() : void{
		$this->enabled = false;
		$this->cancelAllTasks();
	}

	public function setEnabled(bool $enabled) : void{
		$this->enabled = $enabled;
	}
	public function mainThreadHeartbeat($currentTick){
		$this->currentTick = $currentTick;
		while($this->isReady($this->currentTick)){
			$task = $this->queue->extract();
			if($task->isCancelled()){
				unset($this->tasks[$task->getTaskId()]);
				continue;
			}
			$task->run($this->currentTick);
			if(!$task->isCancelled() && $task->isRepeating()){
				$task->setNextRun($this->currentTick + $task->getPeriod());
				$this->queue->insert($task, $this->currentTick + $task->getPeriod());
			}else{
				$task->remove();
				unset($this->tasks[$task->getTaskId()]);
			}
		}
	}

	private function isReady($currentTick){
		return !$this->queue->isEmpty() and $this->queue->current()->getNextRun() <= $currentTick;
	}
	private function nextId(){
		return $this->ids++;
	}
}