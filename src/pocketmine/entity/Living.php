<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;


use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Timings;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;

abstract class Living extends Entity implements Damageable{

	protected $gravity = 0.08;
	protected $drag = 0.02;

	protected $attackTime = 0;
	protected $maxDeadTicks = 10;

	protected $invisible = false;

	protected $jumpVelocity = 0.42;

	protected function initEntity(){
		parent::initEntity();

		$health = $this->getMaxHealth();

		if(isset($this->namedtag->HealF)){
			$health = $this->namedtag["HealF"];
			unset($this->namedtag["HealF"]);
		}elseif(isset($this->namedtag->Health)){
			$healthTag = $this->namedtag->Health;
			$health = (float) $healthTag->getValue();
			if(!($healthTag instanceof FloatTag)){
				unset($this->namedtag->Health);
			}
		}

		$this->setHealth($health);
	}
	public function setHealth(float $amount){
		$wasAlive = $this->isAlive();
		parent::setHealth($amount);
		if($this->isAlive() and !$wasAlive){
			$pk = new EntityEventPacket();
			$pk->entityRuntimeId = $this->getId();
			$pk->event = EntityEventPacket::RESPAWN;
			$this->server->broadcastPacket($this->hasSpawned, $pk);
		}
	}

	public function getMaxHealth() : int{
		return (int) $this->attributeMap->getAttribute(Attribute::HEALTH)->getMaxValue();
	}

	public function setMaxHealth(int $amount){
		$this->attributeMap->getAttribute(Attribute::HEALTH)->setMaxValue($amount);
	}

	public function getAbsorption() : float{
		return $this->attributeMap->getAttribute(Attribute::ABSORPTION)->getValue();
	}

	public function setAbsorption(float $absorption){
		$this->attributeMap->getAttribute(Attribute::ABSORPTION)->setValue($absorption);
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Health = new FloatTag("Health", $this->getHealth());
	}
	public abstract function getName();
	public function hasLineOfSight(Entity $entity){
		return true;
	}
	public function getJumpVelocity() : float{
		return $this->jumpVelocity + ($this->hasEffect(Effect::JUMP) ? (($this->getEffect(Effect::JUMP)->getEffectLevel()) / 10) : 0);
	}
	public function jump() : void{
		if($this->onGround){
			$this->motion->y = $this->getJumpVelocity();
		}
	}
	public function getHighestArmorEnchantmentLevel(int $enchantment) : int{
		$result = 0;
		if (isset($this->inventory)){
			foreach($this->inventory->getArmorContents() as $item){
				$result = max($result, $item->getEnchantmentLevel($enchantment));
			}
		}

		return $result;
	}
	public function attack(EntityDamageEvent $source){
		if($this->noDamageTicks > 0 && $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE){
			$source->setCancelled();
		}elseif($this->attackTime > 0){
			$lastCause = $this->getLastDamageCause();
			if($lastCause !== null and $lastCause->getDamage() >= $source->getDamage()){
				$source->setCancelled();
			}
		}

        $this->applyDamageModifiers($source);

		if($source instanceof EntityDamageByEntityEvent && (
				$source->getCause() === EntityDamageEvent::CAUSE_BLOCK_EXPLOSION ||
				$source->getCause() === EntityDamageEvent::CAUSE_ENTITY_EXPLOSION)
		){
			$base = $source->getKnockBack();
			$source->setKnockBack($base - min($base, $base * $this->getHighestArmorEnchantmentLevel(Enchantment::TYPE_ARMOR_EXPLOSION_PROTECTION) * 0.15));
		}

		parent::attack($source);

		if($source->isCancelled()){
			return;
		}

        $this->attackTime = 10;

		if($source instanceof EntityDamageByChildEntityEvent){
			$e = $source->getChild();
			if($e !== null){
				$motion = $e->getMotion();
				$this->knockBack($e, $source->getDamage(), $motion->x, $motion->z, $source->getKnockBack());
			}
		}elseif($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			if($e !== null){
				$deltaX = $this->x - $e->x;
				$deltaZ = $this->z - $e->z;
				$this->knockBack($e, $source->getDamage(), $deltaX, $deltaZ, $source->getKnockBack());
			}

			if($e instanceof Husk){
				$this->addEffect(Effect::getEffect(Effect::HUNGER)->setDuration(7 * 20 * $this->level->getDifficulty()));
			}
		}

        $this->applyPostDamageEffects($source);

        if ($this->isAlive()){
            $pk = new EntityEventPacket();
            $pk->entityRuntimeId = $this->getId();
            $pk->event = EntityEventPacket::HURT_ANIMATION;
            $this->server->broadcastPacket($this->hasSpawned, $pk);
        }else{
            $pk = new EntityEventPacket();
            $pk->entityRuntimeId = $this->getId();
            $pk->event = EntityEventPacket::DEATH_ANIMATION;
            $this->server->broadcastPacket($this->hasSpawned, $pk);
        }
	}

    protected function applyPostDamageEffects(EntityDamageEvent $source) : void{

    }

    public function applyDamageModifiers(EntityDamageEvent $source) : void{

    }
	public function knockBack(Entity $attacker, $damage, $x, $z, $base = 0.4){
		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}
		if(mt_rand() / mt_getrandmax() > $this->getAttributeMap()->getAttribute(Attribute::KNOCKBACK_RESISTANCE)->getValue()){
			$f = 1 / $f;

			$motion = new Vector3($this->motion->x, $this->motion->y, $this->motion->z);

			$motion->x /= 2;
			$motion->y /= 2;
			$motion->z /= 2;
			$motion->x += $x * $f * $base;
			$motion->y += $base;
			$motion->z += $z * $f * $base;

			if($motion->y > $base){
				$motion->y = $base;
			}

			$this->setMotion($motion);
		}
	}

	protected function addAttributes() : void{
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HEALTH));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::FOLLOW_RANGE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::KNOCKBACK_RESISTANCE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::MOVEMENT_SPEED));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::ATTACK_DAMAGE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::ABSORPTION));
	}

	public function kill(){
		parent::kill();
		$this->callDeathEvent();
	}

	protected function callDeathEvent(){
		$this->server->getPluginManager()->callEvent($ev = new EntityDeathEvent($this, $this->getDrops()));
		foreach($ev->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
	}
	public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
		Timings::$timerLivingEntityBaseTick->startTiming();
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_BREATHING, !$this->isInsideOfWater());

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->isAlive()){
			if($this->isInsideOfSolid()){
				$hasUpdate = true;
				$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 1);
				$this->attack($ev);
			}
			$maxAir = 400 + $EnchantL * 300;
			$this->setDataProperty(self::DATA_MAX_AIR, self::DATA_TYPE_SHORT, $maxAir);
			if(!$this->hasEffect(Effect::WATER_BREATHING) and $this->isInsideOfWater()){
				if($this instanceof WaterAnimal){
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, 400);
				}else{
					$hasUpdate = true;
					$airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
					if($airTicks <= -80){
						$airTicks = 0;

						$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);
						$this->attack($ev);
					}
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, min($airTicks, $maxAir));
				}
			}else{
				if($this instanceof WaterAnimal){
					$hasUpdate = true;
					$airTicks = $this->getDataProperty(self::DATA_AIR) - $tickDiff;
					if($airTicks <= -80){
						$airTicks = 0;

						$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 2);
						$this->attack($ev);
					}
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $airTicks);
				}else{
					$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $maxAir);
				}
			}
		}

		if($this->attackTime > 0){
			$this->attackTime -= $tickDiff;
		}

		Timings::$timerLivingEntityBaseTick->stopTiming();

		return $hasUpdate;
	}
	public function getDrops(){
		return [];
	}
	public function getLineOfSight($maxDistance, $maxLength = 0, array $transparent = []){
		if($maxDistance > 120){
			$maxDistance = 120;
		}

		if(count($transparent) === 0){
			$transparent = null;
		}

		$blocks = [];
		$nextIndex = 0;

		foreach(VoxelRayTrace::inDirection($this->add(0, $this->eyeHeight, 0), $this->getDirectionVector(), $maxDistance) as $vector3){
			$block = $this->level->getBlockAt($vector3->x, $vector3->y, $vector3->z);
			$blocks[$nextIndex++] = $block;

			if($maxLength !== 0 and count($blocks) > $maxLength){
				array_shift($blocks);
				--$nextIndex;
			}

			$id = $block->getId();

			if($transparent === null){
				if($id !== 0){
					break;
				}
			}else{
				if(!isset($transparent[$id])){
					break;
				}
			}
		}

		return $blocks;
	}
	public function getTargetBlock($maxDistance, array $transparent = []){
		$line = $this->getLineOfSight($maxDistance, 1, $transparent);
		if(!empty($line)){
			return array_shift($line);
		}

		return null;
	}
	public function lookAt(Living $entity, Vector3 $target) : void{
		$horizontal = sqrt(($target->x - $entity->x) ** 2 + ($target->z - $entity->z) ** 2);
		$vertical = $target->y - ($entity->y + $this->getEyeHeight());
		$entity->pitch = -atan2($vertical, $horizontal) / M_PI * 180;

		$xDist = $target->x - $entity->x;
		$zDist = $target->z - $entity->z;
		$entity->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($entity->yaw < 0){
			$entity->yaw += 360.0;
		}
	}
}