<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use function call_user_func_array;
class CallbackTask extends Task{
	protected $callable;
	protected $args;

	public function __construct(callable $callable, array $args = []){
		$this->callable = $callable;
		$this->args = $args;
		$this->args[] = $this;
	}

	public function getCallable(){
		return $this->callable;
	}

	public function onRun($currentTick){
		call_user_func_array($this->callable, $this->args);
	}
}