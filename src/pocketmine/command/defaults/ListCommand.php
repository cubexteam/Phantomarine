<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;


class ListCommand extends VanillaCommand{
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.list.description",
			"%pocketmine.command.players.usage"
		);
		$this->setPermission("pocketmine.command.list");
	}
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$playerNames = array_map(function (Player $player){
			return $player->getName();
		}, array_filter($sender->getServer()->getOnlinePlayers(), function (Player $player) use ($sender){
			return $player->isOnline() and (!($sender instanceof Player) or $sender->canSee($player));
		}));

		$sender->sendMessage(new TranslationContainer("commands.players.list", [count($playerNames), $sender->getServer()->getMaxPlayers()]));
		$sender->sendMessage(implode(", ", $playerNames));

		return true;
	}
}