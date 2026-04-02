<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
interface SourceInterface{
	public function start();
	public function putPacket(Player $player, DataPacket $packet, bool $needACK = false, bool $immediate = true);
	public function close(Player $player, $reason = "unknown reason");
	public function setName(string $name);
	public function process() : void;

	public function shutdown();
	public function emergencyShutdown();

}