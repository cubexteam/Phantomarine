<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\plugin;

use pocketmine\event\Event;
use pocketmine\plugin\Plugin;


abstract class PluginEvent extends Event{
	private $plugin;
	public function __construct(Plugin $plugin){
		$this->plugin = $plugin;
	}
	public function getPlugin(){
		return $this->plugin;
	}
}
