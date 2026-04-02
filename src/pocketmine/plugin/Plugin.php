<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\plugin;

use pocketmine\command\CommandExecutor;
use pocketmine\scheduler\TaskScheduler;
interface Plugin extends CommandExecutor{
	public function onLoad();
	public function onEnable();
	public function isEnabled();
	public function onDisable();
	public function isDisabled();
	public function getDataFolder();
	public function getDescription();
	public function getResource($filename);
	public function saveResource($filename, $replace = false);
	public function getResources();
	public function getConfig();
	public function saveConfig();
	public function saveDefaultConfig();
	public function reloadConfig();
	public function getServer();
	public function getName();
	public function getLogger();
	public function getPluginLoader();
	public function getScheduler() : TaskScheduler;

}