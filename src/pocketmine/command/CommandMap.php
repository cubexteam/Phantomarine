<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command;


interface CommandMap{
	public function registerAll($fallbackPrefix, array $commands);
	public function register($fallbackPrefix, Command $command, $label = null);
	public function dispatch(CommandSender $sender, $cmdLine);
	public function clearCommands();
	public function getCommand($name);


}