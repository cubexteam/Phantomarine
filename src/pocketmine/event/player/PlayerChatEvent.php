<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event\player;

use pocketmine\command\CommandSender;
use pocketmine\event\Cancellable;
use pocketmine\Player;
use pocketmine\Server;
use function spl_object_id;
class PlayerChatEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;
	protected $message;
	protected $format;
	protected $recipients = [];
	public function __construct(Player $player, $message, $format = "chat.type.text", array $recipients = null){
		$this->player = $player;
		$this->message = $message;

		$this->format = $format;

		if($recipients === null){
			foreach(Server::getInstance()->getPluginManager()->getPermissionSubscriptions(Server::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof CommandSender){
					$this->recipients[spl_object_id($permissible)] = $permissible;
				}
			}
		}else{
			$this->recipients = $recipients;
		}
	}
	public function getMessage(){
		return $this->message;
	}
	public function setMessage($message){
		$this->message = $message;
	}
	public function setPlayer(Player $player){
		$this->player = $player;
	}
	public function getFormat(){
		return $this->format;
	}
	public function setFormat($format){
		$this->format = $format;
	}
	public function getRecipients(){
		return $this->recipients;
	}
	public function setRecipients(array $recipients){
		$this->recipients = $recipients;
	}
}