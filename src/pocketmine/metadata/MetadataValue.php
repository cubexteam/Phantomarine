<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\metadata;

use pocketmine\plugin\Plugin;

abstract class MetadataValue{
	protected $owningPlugin;
	protected function __construct(Plugin $owningPlugin){
		$this->owningPlugin = new \WeakRef($owningPlugin);
	}
	public function getOwningPlugin(){
		return $this->owningPlugin->get();
	}
	public abstract function value();
	public abstract function invalidate();
}