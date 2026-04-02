<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\server;

use pocketmine\command\CommandSender;
class RemoteServerCommandEvent extends ServerCommandEvent{
	public static $handlerList = null;
	public function __construct(CommandSender $sender, $command){
		parent::__construct($sender, $command);
	}

}