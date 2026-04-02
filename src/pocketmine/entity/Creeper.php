<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\CreeperPowerEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class Creeper extends Monster{
	const NETWORK_ID = 33;

	const DATA_SWELL = 19;
	const DATA_SWELL_OLD = 20;
	const DATA_SWELL_DIRECTION = 21;

	public $width = 0.6;
	public $height = 1.8;

	public $dropExp = [5, 5];

	public $drag = 0.2;
	public $gravity = 0.3;
	public function getName() : string{
		return "Creeper";
	}

	public function initEntity(){
		parent::initEntity();

		if(!isset($this->namedtag->powered)){
			$this->setPowered(false);
		}
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_POWERED, $this->isPowered());
	}
	public function setPowered(bool $powered, Lightning $lightning = null){
		if($lightning != null){
			$powered = true;
			$cause = CreeperPowerEvent::CAUSE_LIGHTNING;
		}else $cause = $powered ? CreeperPowerEvent::CAUSE_SET_ON : CreeperPowerEvent::CAUSE_SET_OFF;

		$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new CreeperPowerEvent($this, $lightning, $cause));

		if(!$ev->isCancelled()){
			$this->namedtag->powered = new ByteTag("powered", $powered ? 1 : 0);
			$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_POWERED, $powered);
		}
	}
	public function isPowered() : bool{
		return (bool) $this->namedtag["powered"];
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Creeper::NETWORK_ID;
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
		$drops = [];
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
				if(mt_rand(0, 40) < (5 + 2 * $lootingL)){
					switch(mt_rand(0, 2)){
						case 0:
							$drops[] = ItemItem::get(ItemItem::TOTEM, 0, 1);
							break;
						case 1:
							$drops[] = ItemItem::get(ItemItem::GOLD_NUGGET, 0, 1);
							break;
					}
				}
				$count = mt_rand(0, 2 + $lootingL);
				if($count > 0){
					$drops[] = ItemItem::get(ItemItem::GUNPOWDER, 0, $count);
				}
			}
		}

		return $drops;
	}
}