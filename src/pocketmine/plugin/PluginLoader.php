<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\plugin;
interface PluginLoader{
	public function loadPlugin($file);
	public function getPluginDescription($file);
	public function getPluginFilters();
	public function canLoadPlugin(string $path) : bool;
	public function enablePlugin(Plugin $plugin);
	public function disablePlugin(Plugin $plugin);


}