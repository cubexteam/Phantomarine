<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;

class Squid extends WaterAnimal{
	const NETWORK_ID = 17;

	public $width = 0.95;
	public $height = 0.95;

	public $dropExp = [1, 3];
	public $swimDirection = null;
	public $swimSpeed = 0.1;

	private $switchDirectionTicker = 0;

	public function initEntity(){
		$this->setMaxHealth(10);
		parent::initEntity();
	}
	public function getName() : string{
		return "Squid";
	}
	public function attack(EntityDamageEvent $source){
		parent::attack($source);
		if($source->isCancelled()){
			return;
		}

		if($source instanceof EntityDamageByEntityEvent){
			$this->swimSpeed = mt_rand(150, 350) / 2000;
			$e = $source->getDamager();
			if($e !== null){
				$this->swimDirection = (new Vector3($this->x - $e->x, $this->y - $e->y, $this->z - $e->z))->normalize();
			}

			$pk = new EntityEventPacket();
			$pk->entityRuntimeId = $this->getId();
			$pk->event = EntityEventPacket::SQUID_INK_CLOUD;
			$this->server->broadcastPacket($this->hasSpawned, $pk);
		}
	}
	private function generateRandomDirection(){
		return new Vector3(mt_rand(-1000, 1000) / 1000, mt_rand(-500, 500) / 1000, mt_rand(-1000, 1000) / 1000);
	}
	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if($this->closed !== false){
			return false;
		}

		if(++$this->switchDirectionTicker === 100 or $this->isCollided){
			$this->switchDirectionTicker = 0;
			if(mt_rand(0, 100) < 50){
				$this->swimDirection = null;
			}
		}

		$hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);

		if(!$this->isFlaggedForDespawn()){

			if($this->y > 62 and $this->swimDirection !== null){
				$this->swimDirection->y = -0.5;
			}

			$inWater = $this->isInsideOfWater();
			if(!$inWater){
				$this->swimDirection = null;
			}elseif($this->swimDirection !== null){
				if($this->motion->x ** 2 + $this->motion->y ** 2 + $this->motion->z ** 2 <= $this->swimDirection->lengthSquared()){
					$this->motion->x = $this->swimDirection->x * $this->swimSpeed;
					$this->motion->y = $this->swimDirection->y * $this->swimSpeed;
					$this->motion->z = $this->swimDirection->z * $this->swimSpeed;
				}
			}else{
				$this->swimDirection = $this->generateRandomDirection();
				$this->swimSpeed = mt_rand(50, 100) / 2000;
			}

			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->yaw = (-atan2($this->motion->x, $this->motion->z) * 180 / M_PI);
			$this->pitch = (-atan2($f, $this->motion->y) * 180 / M_PI);
		}

		return $hasUpdate;
	}

	protected function applyGravity(){
		if(!$this->isInsideOfWater()){
			parent::applyGravity();
		}
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = Squid::NETWORK_ID;
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
		$lootingL = 0;
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);

				$drops = [ItemItem::get(ItemItem::DYE, 0, mt_rand(1, 3 + $lootingL))];

				return $drops;
			}
		}

		return [];
	}
}
