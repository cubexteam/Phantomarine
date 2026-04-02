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

class PigZombie extends Monster{
	const NETWORK_ID = 36;

	public $width = 0.6;
	public $height = 1.9;

	public $drag = 0.2;
	public $gravity = 0.3;

	public $dropExp = [5, 5];
	public function getName() : string{
		return "PigZombie";
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = PigZombie::NETWORK_ID;
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
		$pk->item = new ItemItem(283);
		$pk->inventorySlot = 0;
		$pk->hotbarSlot = 0;

		$player->dataPacket($pk);
	}
	public function getDrops(){
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
				if(mt_rand(1, 200) <= (5 + 2 * $lootingL)){
					$rnd = mt_rand(0, 1);
					if($rnd == 0){
						$drops[] = ItemItem::get(ItemItem::GOLD_INGOT, 0, 1);
					}elseif($rnd == 1){
						$drops[] = ItemItem::get(ItemItem::DRAGONS_BREATH, 0, 1);
					}
				}
				$drops[] = ItemItem::get(ItemItem::GOLD_NUGGET, 0, mt_rand(0, 1 + $lootingL));
				$drops[] = ItemItem::get(ItemItem::ROTTEN_FLESH, 0, mt_rand(0, 1 + $lootingL));

				return $drops;
			}
		}

		return [];
	}
}