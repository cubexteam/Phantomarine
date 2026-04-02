<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */


namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;

abstract class Tool extends Durable{
	const TIER_WOODEN = 1;
	const TIER_GOLD = 2;
	const TIER_STONE = 3;
	const TIER_IRON = 4;
	const TIER_DIAMOND = 5;

	const TYPE_NONE = 0;
	const TYPE_SWORD = 1;
	const TYPE_SHOVEL = 2;
	const TYPE_PICKAXE = 3;
	const TYPE_AXE = 4;
	const TYPE_SHEARS = 5;
	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown"){
		parent::__construct($id, $meta, $count, $name);
	}
	public function getMaxStackSize() : int{
		return 1;
	}
	public function useOn($object, $type = 1){
		if($this->isUnbreakable() or $this->isBroken()){
			return true;
		}

		$unbreakingl = $this->getEnchantmentLevel(Enchantment::TYPE_MINING_DURABILITY);
		$unbreakingl = $unbreakingl > 3 ? 3 : $unbreakingl;
		if(mt_rand(1, $unbreakingl + 1) !== 1){
			return true;
		}

		if($type === 1){
			if($object instanceof Entity){
				if($this->isHoe() !== false or $this->isSword() !== false){
					$this->applyDamage(1);
					return true;
				}elseif($this->isPickaxe() !== false or $this->isAxe() !== false or $this->isShovel() !== false){
					$this->applyDamage(2);
					return true;
				}
				return true;
			}elseif($object instanceof Block){
				if($this->isShears() !== false){
					if($object->getToolType() === Tool::TYPE_SHEARS or $object->getHardness() === 0.0){
						$this->applyDamage(1);
					}
					return true;
				}elseif($object->getHardness() > 0){
					if($this->isSword() !== false){
						$this->applyDamage(2);
						return true;
					}elseif($this->isPickaxe() !== false or $this->isAxe() !== false or $this->isShovel() !== false){
						$this->applyDamage(1);
						return true;
					}
				}
			}
		}elseif($type === 2){
			if($this->isHoe() !== false or $this->id === self::FLINT_STEEL or $this->isShovel() !== false){
				$this->applyDamage(1);
				return true;
			}
		}
		return true;
	}
	public function getMaxDurability(){
		$levels = [
			Tool::TIER_GOLD => 33,
			Tool::TIER_WOODEN => 60,
			Tool::TIER_STONE => 132,
			Tool::TIER_IRON => 251,
			Tool::TIER_DIAMOND => 1562
		];

		if(($type = $this->isPickaxe()) === false){
			if(($type = $this->isAxe()) === false){
				if(($type = $this->isSword()) === false){
					if(($type = $this->isShovel()) === false){
						if(($type = $this->isHoe()) === false){
							$type = $this->id;
						}
					}
				}
			}
		}

		return $levels[$type];
	}

	public function isTool(){
		return true;
	}
}