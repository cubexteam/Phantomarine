<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\block\Liquid;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\ExplodePacket;
use pocketmine\Player;

class Lightning extends Animal{
	const NETWORK_ID = 93;

	public $width = 0.3;
	public $height = 1.8;
	protected $age = 0;
	public function getName() : string{
		return "Lightning";
	}

	public function initEntity(){
		parent::initEntity();
		$this->setMaxHealth(2);
		$this->setHealth(2);
	}
	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);

		$this->age += $tickDiff;
		if($this->age > 20){
			$this->flagForDespawn();
			return true;
		}

		return $hasUpdate;
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

		$pk = new ExplodePacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->radius = 10;
		$pk->records = [];
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	public function spawnToAll(){
		parent::spawnToAll();

		if($this->getLevel()->getServer()->lightningFire){
			$fire = ItemItem::get(ItemItem::FIRE)->getBlock();
			$oldBlock = $this->getLevel()->getBlock($this);
			if($oldBlock instanceof Liquid){

			}elseif($oldBlock->isSolid()){
				$v3 = new Vector3($this->x, $this->y + 1, $this->z);
			}else{
				$v3 = new Vector3($this->x, $this->y, $this->z);
			}
			if(isset($v3)) $this->getLevel()->setBlock($v3, $fire);

			foreach($this->level->getNearbyEntities($this->boundingBox->expandedCopy(4, 3, 4), $this) as $entity){
				if($entity instanceof Player){
					$damage = mt_rand(8, 20);
					$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageByEntityEvent::CAUSE_LIGHTNING, $damage);
					if($entity->attack($ev) === true){
						$ev->useArmors();
					}
					$entity->setOnFire(mt_rand(3, 8));
				}

				if($entity instanceof Creeper){
					$entity->setPowered(true, $this);
				}
			}
		}
	}
}