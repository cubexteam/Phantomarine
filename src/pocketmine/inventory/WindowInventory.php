<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\inventory;

use pocketmine\block\Block;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;

class WindowInventory extends CustomInventory{

	protected $customName = "";

	public function __construct(Player $player, string $customName = ""){
		$this->customName = $customName;
		$holder = new WindowHolder($player->getFloorX(), $player->getFloorY() - 3, $player->getFloorZ(), $this);
		parent::__construct($holder, InventoryType::get(InventoryType::CHEST));
	}

	public function onOpen(Player $who){
		$this->holder = $holder = new WindowHolder($who->getFloorX(), $who->getFloorY() - 3, $who->getFloorZ(), $this);

		$pk = new UpdateBlockPacket();
		$pk->x = $holder->x;
		$pk->y = $holder->y;
		$pk->z = $holder->z;
		$pk->blockId = Block::CHEST;
		$pk->blockData = 0;
		$pk->flags = UpdateBlockPacket::FLAG_ALL;
		$who->dataPacket($pk);

		$c = new CompoundTag("", [
			new StringTag("id", Tile::CHEST),
			new IntTag("x", (int) $holder->x),
			new IntTag("y", (int) $holder->y),
			new IntTag("z", (int) $holder->z),


		]);

		if($this->customName !== ""){
			$c->CustomName = new StringTag("CustomName", TextFormat::RESET . $this->customName);
		}

		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$nbt->setData($c);

		$pk = new BlockEntityDataPacket();
		$pk->x = $holder->x;
		$pk->y = $holder->y;
		$pk->z = $holder->z;
		$pk->namedtag = $nbt->write(true);
		$who->dataPacket($pk);

		parent::onOpen($who);
		$this->sendContents($who);
	}

	public function onClose(Player $who){
		$holder = $this->holder;
		$pk = new UpdateBlockPacket();
		$pk->x = $holder->x;
		$pk->y = $holder->y;
		$pk->z = $holder->z;
		$pk->blockId = $who->getLevel()->getBlockIdAt($holder->x, $holder->y, $holder->z);
		$pk->blockData = $who->getLevel()->getBlockDataAt($holder->x, $holder->y, $holder->z);
		$pk->flags = UpdateBlockPacket::FLAG_ALL;
		$who->dataPacket($pk);
	}
}
