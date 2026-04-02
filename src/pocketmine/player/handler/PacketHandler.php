<?php

namespace pocketmine\player\handler;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;

abstract class PacketHandler{

	protected Player $player;

	public function __construct(Player $player){
		$this->player = $player;
	}
	public abstract function handle(DataPacket $packet) : bool;

}