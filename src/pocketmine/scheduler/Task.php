<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\utils\Utils;

abstract class Task{
	private $taskHandler = null;
	public final function getHandler(){
		return $this->taskHandler;
	}

	public final function getTaskId() : int{
		if($this->taskHandler !== null){
			return $this->taskHandler->getTaskId();
		}

		return -1;
	}

	public function getName() : string{
		return Utils::getNiceClassName($this);
	}
	public final function setHandler($taskHandler){
		if($this->taskHandler === null or $taskHandler === null){
			$this->taskHandler = $taskHandler;
		}
	}
	public abstract function onRun($currentTick);
	public function onCancel(){

	}
}