<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\utils\Utils;
class ClosureTask extends Task{
	private $closure;
	public function __construct(\Closure $closure){
		$this->closure = $closure;
	}

	public function getName() : string{
		return Utils::getNiceClosureName($this->closure);
	}

	public function onRun($currentTick){
		($this->closure)($currentTick);
	}
}