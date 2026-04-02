<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command;


interface CommandExecutor{
	public function onCommand(CommandSender $sender, Command $command, $label, array $args);

}