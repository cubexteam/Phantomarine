<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;

abstract class Event{

	protected $eventName = null;
	private $isCancelled = false;
	final public function getEventName(){
        return $this->eventName ?? get_class($this);
	}
	public function isCancelled(){
		if(!($this instanceof Cancellable)){
			throw new \BadMethodCallException(get_class($this) . " is not Cancellable");
		}

		return $this->isCancelled;
	}
	public function setCancelled($value = true){
		if(!($this instanceof Cancellable)){
			throw new \BadMethodCallException(get_class($this) . " is not Cancellable");
		}

		$this->isCancelled = (bool) $value;
	}
	public function getHandlers(){
		if(static::$handlerList === null){
			static::$handlerList = new HandlerList();
		}

		return static::$handlerList;
	}

}