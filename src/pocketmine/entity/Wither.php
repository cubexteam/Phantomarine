<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Wither extends Animal{
	const NETWORK_ID = 52;

	public $width = 1.0;
	public $height = 3.0;

	public $dropExp = [25, 50];
	private $boomTicks = 0;
	public function getName() : string{
		return "Wither";
	}

	public function initEntity(){
		$this->setMaxHealth(300);
		parent::initEntity();
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Wither::NETWORK_ID;
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
	public function getDrops(){
		$drops = [ItemItem::get(ItemItem::NETHER_STAR, 0, 1)];
		return $drops;
	}

	public function getBombNBT() : CompoundTag{
		return Entity::createBaseNBT($this->add(0, 2, 0), new Vector3(0, 0, 0), $this->yaw, $this->pitch);
	}

	public function getBombRightNBT() : CompoundTag{
		return Entity::createBaseNBT($this->add(0, 2, 0), new Vector3(0, 0, 0), $this->yaw + 90, $this->pitch);
	}

	public function getBombLeftNBT() : CompoundTag{
		return Entity::createBaseNBT($this->add(0, 2, 0), new Vector3(0, 0, 0), $this->yaw - 90, $this->pitch);
	}

	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);

		if($this->boomTicks < 40){
			$this->boomTicks++;
		}else{
			$nbt = $this->getBombNBT();
			$tnt = new WitherTNT($this->level, $nbt);
			$tnt->spawnToAll();

			$nbtright = $this->getBombRightNBT();
			$tntright = new WitherTNT($this->level, $nbtright);
			$tntright->spawnToAll();

			$nbtleft = $this->getBombLeftNBT();
			$tntleft = new WitherTNT($this->level, $nbtleft);
			$tntleft->spawnToAll();

			$this->flagForDespawn();
		}

		return $hasUpdate;
	}
}
