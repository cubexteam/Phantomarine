<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Pig extends Animal{
	const NETWORK_ID = 12;

	public $width = 0.9;
	public $height = 0.9;

	public $dropExp = [1, 3];
	public function getName() : string{
		return "Pig";
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Pig::NETWORK_ID;
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
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
				$drops = [ItemItem::get(ItemItem::RAW_PORKCHOP, 0, mt_rand(1, 3 + $lootingL))];

				return $drops;
			}
		}

		return [];
	}
}