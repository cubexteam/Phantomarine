<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use function max;
use function min;

class Attribute{

	public const ABSORPTION = 0;
	public const SATURATION = 1;
	public const EXHAUSTION = 2;
	public const KNOCKBACK_RESISTANCE = 3;
	public const HEALTH = 4;
	public const MOVEMENT_SPEED = 5;
	public const FOLLOW_RANGE = 6;
	public const HUNGER = 7;
	public const FOOD = 7;
	public const ATTACK_DAMAGE = 8;
	public const EXPERIENCE_LEVEL = 9;
	public const EXPERIENCE = 10;

	public const HORSE_JUMP_STRENGTH = 20;
	private $id;
	protected $minValue;
	protected $maxValue;
	protected $defaultValue;
	protected $currentValue;
	protected $name;
	protected $shouldSend;
	protected $desynchronized = true;
	protected static $attributes = [];

	public static function init(){
		self::addAttribute(self::ABSORPTION, "minecraft:absorption", 0.00, 340282346638528859811704183484516925440.00, 0.00);
		self::addAttribute(self::SATURATION, "minecraft:player.saturation", 0.00, 20.00, 20.00);
		self::addAttribute(self::EXHAUSTION, "minecraft:player.exhaustion", 0.00, 5.00, 0.0, false);
		self::addAttribute(self::KNOCKBACK_RESISTANCE, "minecraft:knockback_resistance", 0.00, 1.00, 0.00);
		self::addAttribute(self::HEALTH, "minecraft:health", 0.00, 20.00, 20.00);
		self::addAttribute(self::MOVEMENT_SPEED, "minecraft:movement", 0.00, 340282346638528859811704183484516925440.00, 0.10);
		self::addAttribute(self::FOLLOW_RANGE, "minecraft:follow_range", 0.00, 2048.00, 16.00, false);
		self::addAttribute(self::HUNGER, "minecraft:player.hunger", 0.00, 20.00, 20.00);
		self::addAttribute(self::ATTACK_DAMAGE, "minecraft:attack_damage", 0.00, 340282346638528859811704183484516925440.00, 1.00, false);
		self::addAttribute(self::EXPERIENCE_LEVEL, "minecraft:player.level", 0.00, 24791.00, 0.00);
		self::addAttribute(self::EXPERIENCE, "minecraft:player.experience", 0.00, 1.00, 0.00);
		self::addAttribute(self::HORSE_JUMP_STRENGTH, "minecraft:horse.jump_strength", 0.4, 1, 0.70);
	}
	public static function addAttribute($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend = true){
		if($minValue > $maxValue or $defaultValue > $maxValue or $defaultValue < $minValue){
			throw new \InvalidArgumentException("Invalid ranges: min value: $minValue, max value: $maxValue, $defaultValue: $defaultValue");
		}

		return self::$attributes[(int) $id] = new Attribute($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend);
	}
	public static function getAttribute($id){
		return isset(self::$attributes[$id]) ? clone self::$attributes[$id] : null;
	}
	public static function getAttributeByName($name){
		foreach(self::$attributes as $a){
			if($a->getName() === $name){
				return clone $a;
			}
		}

		return null;
	}
	public function __construct($id, $name, $minValue, $maxValue, $defaultValue, $shouldSend = true){
		$this->id = (int) $id;
		$this->name = (string) $name;
		$this->minValue = (float) $minValue;
		$this->maxValue = (float) $maxValue;
		$this->defaultValue = (float) $defaultValue;
		$this->shouldSend = (bool) $shouldSend;

		$this->currentValue = $this->defaultValue;
	}
	public function getMinValue(){
		return $this->minValue;
	}
	public function setMinValue($minValue){
		if($minValue > ($max = $this->getMaxValue())){
			throw new \InvalidArgumentException("Minimum $minValue is greater than the maximum $max");
		}

		if($this->minValue != $minValue){
			$this->desynchronized = true;
			$this->minValue = $minValue;
		}
		return $this;
	}
	public function getMaxValue(){
		return $this->maxValue;
	}
	public function setMaxValue($maxValue){
		if($maxValue < ($min = $this->getMinValue())){
			throw new \InvalidArgumentException("Maximum $maxValue is less than the minimum $min");
		}

		if($this->maxValue != $maxValue){
			$this->desynchronized = true;
			$this->maxValue = $maxValue;
		}
		return $this;
	}
	public function getDefaultValue(){
		return $this->defaultValue;
	}
	public function setDefaultValue($defaultValue){
		if($defaultValue > $this->getMaxValue() or $defaultValue < $this->getMinValue()){
			throw new \InvalidArgumentException("Default $defaultValue is outside the range " . $this->getMinValue() . " - " . $this->getMaxValue());
		}

		if($this->defaultValue !== $defaultValue){
			$this->desynchronized = true;
			$this->defaultValue = $defaultValue;
		}
		return $this;
	}

	public function resetToDefault(){
		$this->setValue($this->getDefaultValue(), true);
	}
	public function getValue(){
		return $this->currentValue;
	}
	public function setValue($value, bool $fit = false, bool $shouldSend = false){
		if($value > $this->getMaxValue() or $value < $this->getMinValue()){
			if(!$fit){
				throw new \InvalidArgumentException("Value $value is outside the range " . $this->getMinValue() . " - " . $this->getMaxValue());
			}
			$value = min(max($value, $this->getMinValue()), $this->getMaxValue());
		}

		if($this->currentValue != $value){
			$this->desynchronized = true;
			$this->currentValue = $value;
		}elseif($shouldSend){
			$this->desynchronized = true;
		}

		return $this;
	}
	public function getName(){
		return $this->name;
	}
	public function getId(){
		return $this->id;
	}
	public function isSyncable(){
		return $this->shouldSend;
	}
	public function isDesynchronized() : bool{
		return $this->shouldSend and $this->desynchronized;
	}
	public function markSynchronized(bool $synced = true){
		$this->desynchronized = !$synced;
	}
}
