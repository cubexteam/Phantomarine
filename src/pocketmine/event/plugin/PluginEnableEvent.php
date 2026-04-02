<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */


namespace pocketmine\event\plugin;

use pocketmine\plugin\Plugin;


class PluginEnableEvent extends PluginEvent{
	public static $handlerList = null;
	public function __construct(Plugin $plugin){
		parent::__construct($plugin);
	}
}
