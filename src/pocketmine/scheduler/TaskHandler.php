<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;

class TaskHandler{
	protected $task;
	protected $taskId;
	protected $delay;
	protected $period;
	protected $nextRun;
	protected $cancelled = false;
	private $timings;
	private $taskName;
	private $ownerName;
	public function __construct(Task $task, int $taskId, int $delay = -1, int $period = -1, ?string $ownerName = null){
		if($task->getHandler() !== null){
			throw new \InvalidArgumentException("Cannot assign multiple handlers to the same task");
		}
		$this->task = $task;
		$this->taskId = $taskId;
		$this->delay = $delay;
		$this->period = $period;
		$this->taskName = $task->getName();
		$this->ownerName = $ownerName ?? "Unknown";
		$this->timings = Timings::getScheduledTaskTimings($this, $period);
		$this->task->setHandler($this);
	}

	public function isCancelled(){
		return $this->cancelled;
	}

	public function getNextRun(){
		return $this->nextRun;
	}
	public function setNextRun($ticks){
		$this->nextRun = $ticks;
	}

	public function getTaskId() : int{
		return $this->taskId;
	}

	public function getTask(){
		return $this->task;
	}

	public function getDelay(){
		return $this->delay;
	}

	public function isDelayed(){
		return $this->delay > 0;
	}

	public function isRepeating(){
		return $this->period > 0;
	}

	public function getPeriod(){
		return $this->period;
	}
	public function cancel(){
		try{
			if(!$this->isCancelled()){
				$this->task->onCancel();
			}
		}finally{
			$this->remove();
		}
	}
	public function remove(){
		$this->cancelled = true;
		$this->task->setHandler(null);
	}
	public function run(int $currentTick){
		$this->timings->startTiming();
		try{
			$this->task->onRun($currentTick);
		}finally{
			$this->timings->stopTiming();
		}
	}

	public function getTaskName(){
		return $this->taskName;
	}

	public function getOwnerName() : string{
		return $this->ownerName;
	}
}