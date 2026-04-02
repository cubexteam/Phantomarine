<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class PluginsCommand extends VanillaCommand{
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.plugins.description",
			"%pocketmine.command.plugins.usage",
			["pl"]
		);
		$this->setPermission("pocketmine.command.plugins");
	}
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$list = array_map(function (Plugin $plugin) : string{
			return ($plugin->isEnabled() ? TextFormat::GREEN : TextFormat::RED) . $plugin->getDescription()->getFullName();
		}, $sender->getServer()->getPluginManager()->getPlugins());
		$sender->sendMessage(new TranslationContainer("pocketmine.command.plugins.success", [count($list), implode(TextFormat::WHITE . ", ", $list)]));
		return true;
	}
}
