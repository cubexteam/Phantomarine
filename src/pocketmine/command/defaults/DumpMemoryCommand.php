<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;


class DumpMemoryCommand extends VanillaCommand{
	public function __construct($name){
		parent::__construct(
			$name,
			"Dumps the memory",
			"/$name [path]"
		);
		$this->setPermission("pocketmine.command.dumpmemory");
	}
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$sender->getServer()->getMemoryManager()->dumpServerMemory($args[0] ?? ($sender->getServer()->getDataPath() . "/memory_dumps/" . date("D_M_j-H.i.s-T_Y")), 48, 80);
		return true;
	}

}
