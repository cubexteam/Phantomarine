<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command;

use pocketmine\permission\Permissible;

interface CommandSender extends Permissible{
	public function sendMessage($message);
	public function getServer();
	public function getName();


}