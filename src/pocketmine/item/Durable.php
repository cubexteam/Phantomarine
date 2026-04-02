<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\nbt\tag\ByteTag;

abstract class Durable extends Item
{
	public function isUnbreakable(): bool
	{
		$tag = $this->getNamedTagEntry("Unbreakable");
		return $tag !== null and $tag->getValue() !== 0;
	}
	public function setUnbreakable(bool $value = true)
	{
		$this->setNamedTagEntry(new ByteTag("Unbreakable", $value ? 1 : 0));
	}
	public function applyDamage(int $amount) : bool{
		if ($this->isUnbreakable() or $this->isBroken()) {
			return false;
		}

		$amount -= $this->getUnbreakingDamageReduction($amount);

		$this->meta = min($this->meta + $amount, $this->getMaxDurability());
		if ($this->isBroken()) {
			$this->pop();
		}

		return true;
	}

	protected function getUnbreakingDamageReduction(int $amount) : int{
		if(($unbreakingLevel = $this->getEnchantmentLevel(Enchantment::TYPE_MINING_DURABILITY)) > 0){
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for($i = 0; $i < $amount; ++$i){
				if(lcg_value() > $chance){
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}
	public function isBroken(): bool
	{
		return $this->meta >= $this->getMaxDurability() || $this->isNull();
	}
}