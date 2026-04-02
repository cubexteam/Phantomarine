<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\level\Level;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Ocelot extends Animal{
	const NETWORK_ID = 22;

	const DATA_CAT_TYPE = 18;

	const TYPE_WILD = 0;
	const TYPE_TUXEDO = 1;
	const TYPE_TABBY = 2;
	const TYPE_SIAMESE = 3;

	public $width = 0.6;
	public $height = 0.7;

	public $dropExp = [1, 3];
	public function getName() : string{
		return "Ocelot";
	}
	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->CatType)){
			$nbt->CatType = new ByteTag("CatType", mt_rand(0, 3));
		}
		parent::__construct($level, $nbt);

		$this->setDataProperty(self::DATA_CAT_TYPE, self::DATA_TYPE_BYTE, $this->getCatType());
	}
	public function setCatType(int $type){
		$this->namedtag->CatType = new ByteTag("CatType", $type);
	}
	public function getCatType() : int{
		return (int) $this->namedtag["CatType"];
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = self::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motion->x;
		$pk->speedY = $this->motion->y;
		$pk->speedZ = $this->motion->z;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}
