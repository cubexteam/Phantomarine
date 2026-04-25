<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\scheduler;

use pocketmine\plugin\Plugin;

/**
 * @deprecated
 */
abstract class PluginTask extends Task{

	/** @var Plugin */
	protected $owner;

	/**
	 * @param Plugin $owner
	 */
	public function __construct(Plugin $owner){
		$this->owner = $owner;
	}

	/**
	 * @return Plugin
	 */
	public final function getOwner() : Plugin{
		return $this->owner;
	}
}
