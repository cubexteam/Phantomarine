<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\level\Level;

class DifficultyCommand extends VanillaCommand{
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.difficulty.description",
			"%commands.difficulty.usage"
		);
		$this->setPermission("pocketmine.command.difficulty");
	}
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) !== 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$difficulty = Level::getDifficultyFromString($args[0]);

		if($sender->getServer()->isHardcore()){
			$difficulty = Level::DIFFICULTY_HARD;
		}

		if($difficulty !== -1){
			$sender->getServer()->setConfigInt("difficulty", $difficulty);

			foreach($sender->getServer()->getLevels() as $level){
				$level->setDifficulty($difficulty);
			}

			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.difficulty.success", [$difficulty]));
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		return true;
	}
}