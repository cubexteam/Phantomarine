<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\Player;

class RakLibCommand extends VanillaCommand{
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.raklib.description",
			"%pocketmine.command.raklib.usage"
		);
		$this->setPermission("pocketmine.command.raklib");
	}
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$argCommand = array_shift($args);
		$ip = array_shift($args);
		if($argCommand == "block"){
			if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $ip)){
				$sender->getServer()->getNetwork()->blockAddress($ip, 500);
			}else{
				if(($player = $sender->getServer()->getPlayer($ip)) instanceof Player){
					$sender->getServer()->getNetwork()->blockAddress($player->getAddress(), 500);
				}else{
					$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.invalid"));

					return false;
				}
			}
		}elseif($argCommand == "unblock"){
			if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $ip)){
				$sender->getServer()->getNetwork()->unblockAddress($ip);
			}else{
				$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.invalid"));

				return false;
			}
		}elseif($argCommand == "isblocked"){
			if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $ip)){
				if($sender->getServer()->getNetwork()->isBlockedAddress($ip)){
					$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.blocked", [$ip]));
				}else{
					$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.notBlocked", [$ip]));
				}
			}else{
				$sender->sendMessage(new TranslationContainer("pocketmine.command.raklib.invalid"));

				return false;
			}
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		return true;
	}
}
