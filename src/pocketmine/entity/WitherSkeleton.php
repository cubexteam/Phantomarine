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
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;

class WitherSkeleton extends Monster{
	const NETWORK_ID = 48;

	public $width = 0.864;
	public $height = 2.412;

	public $dropExp = [15, 30];
	public function getName(){
		return "Wither Skeleton";
	}

	public function initEntity(){
		$this->setMaxHealth(20);
		parent::initEntity();
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = WitherSkeleton::NETWORK_ID;
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

		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->item = new ItemItem(272);
		$pk->inventorySlot = 0;
		$pk->hotbarSlot = 0;

		$player->dataPacket($pk);
	}
	public function getDrops(){
		$cause = $this->lastDamageCause;
		$drops = [];
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
				if(mt_rand(1, 200) <= (5 + 2 * $lootingL)){
					$drops[] = ItemItem::get(ItemItem::SKULL, 1, 1);
				}
				$drops[] = ItemItem::get(ItemItem::BONE, 0, mt_rand(0, 2));
				$drops[] = ItemItem::get(ItemItem::COAL, 0, mt_rand(0, 1));
			}elseif($damager instanceof Creeper){
				if(($damager->isPowered()) and ($cause->getCause() == 10)){
					$drops[] = ItemItem::get(ItemItem::SKULL, 1, 1);
				}else{
					$drops[] = ItemItem::get(ItemItem::BONE, 0, mt_rand(0, 2));
					$drops[] = ItemItem::get(ItemItem::COAL, 0, mt_rand(0, 1));
				}
			}
		}

		return $drops;
	}
}