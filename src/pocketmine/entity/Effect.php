<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\Player;
use pocketmine\utils\Config;

class Effect{

	const SPEED = 1;
	const SLOWNESS = 2;
	const HASTE = 3;
	const FATIGUE = 4, MINING_FATIGUE = 4;
	const STRENGTH = 5;
	const INSTANT_HEALTH = 6, HEALING = 6;
	const INSTANT_DAMAGE = 7, HARMING = 7;
	const JUMP_BOOST = 8, JUMP = 8;
	const NAUSEA = 9, CONFUSION = 9;
	const REGENERATION = 10;
	const RESISTANCE = 11, DAMAGE_RESISTANCE = 11;
	const FIRE_RESISTANCE = 12;
	const WATER_BREATHING = 13;
	const INVISIBILITY = 14;
	const BLINDNESS = 15;
	const NIGHT_VISION = 16;
	const HUNGER = 17;
	const WEAKNESS = 18;
	const POISON = 19;
	const WITHER = 20;
	const HEALTH_BOOST = 21;
	const ABSORPTION = 22;
	const SATURATION = 23;
	const LEVITATION = 24;
	protected static $effects = [];

	public static function init(){
		$config = new Config(\pocketmine\PATH . "src/pocketmine/resources/effects.json", Config::JSON, []);

		foreach($config->getAll() as $name => $data){
			$color = hexdec(substr($data["color"], 3));
			$r = ($color >> 16) & 0xff;
			$g = ($color >> 8) & 0xff;
			$b = $color & 0xff;
			self::registerEffect($name, new Effect(
				$data["id"],
				"%potion." . $data["name"],
				$r,
				$g,
				$b,
				$data["isBad"] ?? false,
				$data["default_duration"] ?? 300 * 20,
				$data["has_bubbles"] ?? true
			));
		}
	}

	public static function registerEffect(string $internalName, Effect $effect){
		self::$effects[$effect->getId()] = $effect;
		self::$effects[$internalName] = $effect;
	}
	public static function getEffect($id){
		if(isset(self::$effects[$id])){
			return clone self::$effects[(int) $id];
		}
		return null;
	}
	public static function getEffectByName($name){
		if(isset(self::$effects[$name])){
			return clone self::$effects[$name];
		}
		return null;
	}
	protected $id;

	protected $name;

	protected $duration;

	protected $amplifier = 0;

	protected $color;

	protected $show = true;

	protected $ambient = false;

	protected $bad;

	protected $defaultDuration = 300 * 20;

	protected $hasBubbles = true;
	public function __construct($id, $name, $r, $g, $b, $isBad = false, int $defaultDuration = 300 * 20, bool $hasBubbles = true){
		$this->id = $id;
		$this->name = $name;
		$this->bad = (bool) $isBad;
		$this->setColor($r, $g, $b);
		$this->defaultDuration = $defaultDuration;
		$this->duration = $defaultDuration;
		$this->hasBubbles = $hasBubbles;
	}
	public function getName() : string{
		return $this->name;
	}
	public function getId(){
		return $this->id;
	}
	public function setDuration(int $ticks){
		if($ticks < 0 or $ticks > INT32_MAX){
			throw new \InvalidArgumentException("Effect duration must be in range 0 - " . INT32_MAX . ", got $ticks");
		}
		$this->duration = $ticks;
		return $this;
	}

	public function getDuration(){
		return $this->duration;
	}
	public function getDefaultDuration() : int{
		return $this->defaultDuration;
	}
	public function hasBubbles() : bool{
		return $this->hasBubbles;
	}
	public function isVisible(){
		return $this->show;
	}
	public function setVisible($bool){
		$this->show = (bool) $bool;
		return $this;
	}
	public function getEffectLevel() : int{
		return $this->amplifier + 1;
	}
	public function hasExpired() : bool{
		return $this->duration <= 0;
	}
	public function getAmplifier(){
		return $this->amplifier;
	}
	public function setAmplifier(int $amplifier){
		$this->amplifier = ($amplifier & 0xff);
		return $this;
	}
	public function isAmbient(){
		return $this->ambient;
	}
	public function setAmbient($ambient = true){
		$this->ambient = (bool) $ambient;
		return $this;
	}
	public function isBad(){
		return $this->bad;
	}
	public function canTick(){
		if($this->amplifier < 0) $this->amplifier = 0;
		switch($this->id){
			case Effect::POISON:
				if(($interval = (25 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::WITHER:
				if(($interval = (50 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::REGENERATION:
				if(($interval = (40 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::HUNGER:
				return true;
			case Effect::HEALING:
			case Effect::HARMING:
				return true;
			case Effect::SATURATION:
				if(($interval = (20 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::INSTANT_DAMAGE:
			case Effect::INSTANT_HEALTH:
				return true;
		}
		return false;
	}
	public function applyEffect(Entity $entity){
		switch($this->id){
			case Effect::POISON:
				if($entity->getHealth() > 1){
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
					$entity->attack($ev);
				}
				break;

			case Effect::WITHER:
				$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
				$entity->attack($ev);
				break;

			case Effect::REGENERATION:
				if($entity->getHealth() < $entity->getMaxHealth()){
					$ev = new EntityRegainHealthEvent($entity, 1, EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev);
				}
				break;
			case Effect::HUNGER:
				if($entity instanceof Human){
					$entity->exhaust(0.025 * $this->getEffectLevel(), PlayerExhaustEvent::CAUSE_POTION);
				}
				break;
			case Effect::INSTANT_HEALTH:
				if($entity->getHealth() < $entity->getMaxHealth()){
					$amount = 2 * (2 ** ($this->getEffectLevel() % 32));
					$entity->heal(new EntityRegainHealthEvent($entity, $amount, EntityRegainHealthEvent::CAUSE_MAGIC));
				}
				break;
			case Effect::INSTANT_DAMAGE:
				$amount = 2 * (2 ** ($this->getEffectLevel() % 32));
				$entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, $amount));
				break;
			case Effect::HEALING:
				$level = $this->getEffectLevel();
				if(($entity->getHealth() + 4 * $level) <= $entity->getMaxHealth()){
					$ev = new EntityRegainHealthEvent($entity, 4 * $level, EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev);
				}else{
					$ev = new EntityRegainHealthEvent($entity, $entity->getMaxHealth() - $entity->getHealth(), EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev);
				}
				break;
			case Effect::HARMING:
				$level = $this->getEffectLevel();
				if(($entity->getHealth() - 6 * $level) >= 0){
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 6 * $level);
					$entity->attack($ev);
				}else{
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, $entity->getHealth());
					$entity->attack($ev);
				}
				break;
			case Effect::SATURATION:
				if($entity instanceof Player){
					if($entity->getServer()->foodEnabled){
						$entity->addFood($this->getEffectLevel());
						$entity->addSaturation($this->getEffectLevel() * 2);
					}
				}
				break;
		}
	}
	public function getColor(){
		return [$this->color >> 16, ($this->color >> 8) & 0xff, $this->color & 0xff];
	}
	public function setColor($r, $g, $b){
		$this->color = (($r & 0xff) << 16) + (($g & 0xff) << 8) + ($b & 0xff);
	}
	public function add(Entity $entity, $modify = false, Effect $oldEffect = null){
		$entity->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityEffectAddEvent($entity, $this, $modify, $oldEffect));
		if($ev->isCancelled()){
			return;
		}
		if($entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->entityRuntimeId = $entity->getId();
			$pk->effectId = $this->getId();
			$pk->amplifier = $this->getAmplifier();
			$pk->particles = $this->isVisible();
			$pk->duration = $this->getDuration();
			if($ev->willModify()){
				$pk->eventId = MobEffectPacket::EVENT_MODIFY;
			}else{
				$pk->eventId = MobEffectPacket::EVENT_ADD;
			}

			$entity->dataPacket($pk);

			switch($this->id){
				case Effect::INVISIBILITY:
					$entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
					$entity->setNameTagVisible(false);
					break;
				case Effect::SPEED:
					$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
					if($ev->willModify() and $oldEffect !== null){
						$speed = $attr->getValue() / (1 + 0.2 * $oldEffect->getAmplifier());
					}else{
						$speed = $attr->getValue();
					}
					$speed *= (1 + 0.2 * $this->getEffectLevel());
					$attr->setValue($speed);
					break;
				case Effect::SLOWNESS:
					$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
					if($ev->willModify() and $oldEffect !== null){
						$speed = $attr->getValue() / (1 - 0.15 * $oldEffect->getAmplifier());
					}else{
						$speed = $attr->getValue();
					}
					$speed *= (1 - 0.15 * $this->getEffectLevel());
					$attr->setValue($speed, true);
					break;
				case Effect::HEALTH_BOOST:
					$attr = $entity->getAttributeMap()->getAttribute(Attribute::HEALTH);
					if($ev->willModify() and $oldEffect !== null){
						$max = $attr->getMaxValue() - (4 * $oldEffect->getEffectLevel());
					}else{
						$max = $attr->getMaxValue();
					}

					$max += (4 * $this->getEffectLevel());
					$attr->setMaxValue($max);
					break;
				case Effect::ABSORPTION:
					$new = (4 * $this->getEffectLevel());
					if($new > $entity->getAbsorption()){
						$entity->setAbsorption($new);
					}
					break;
			}
		}
	}
	public function remove(Entity $entity){
		$entity->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityEffectRemoveEvent($entity, $this));
		if($ev->isCancelled()){
			return;
		}
		if($entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->entityRuntimeId = $entity->getId();
			$pk->eventId = MobEffectPacket::EVENT_REMOVE;
			$pk->effectId = $this->getId();

			$entity->dataPacket($pk);

			switch($this->id){
				case Effect::INVISIBILITY:
					$entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
					$entity->setNameTagVisible(true);
					break;
				case Effect::SPEED:
					$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
					$attr->setValue($attr->getValue() / (1 + 0.2 * $this->getEffectLevel()));
					break;
				case Effect::SLOWNESS:
					$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
					$attr->setValue($attr->getValue() / (1 - 0.15 * $this->getEffectLevel()));
					break;
				case Effect::HEALTH_BOOST:
					$attr = $entity->getAttributeMap()->getAttribute(Attribute::HEALTH);
					$attr->setMaxValue($attr->getMaxValue() - 4 * $this->getEffectLevel());
					break;
				case Effect::ABSORPTION:
					$entity->setAbsorption(0);
					break;
			}
		}
	}
}
