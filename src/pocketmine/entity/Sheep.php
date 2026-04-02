<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\block\BlockIds;
use pocketmine\block\Wool;
use pocketmine\entity\feature\Interactive;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemIds;
use pocketmine\item\Shears;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\utils\Random;

class Sheep extends Animal implements Colorable, Interactive{
	const NETWORK_ID = 13;

	public $width = 0.9;
	public $height = 1.3;

	protected function initEntity(){
		parent::initEntity();

		$this->setSheared($this->namedtag->getBoolean("Sheared"));
		$this->setColor($this->namedtag->getByte("Color") ?: self::getRandomColor());
	}

	public function saveNBT(){
		parent::saveNBT();

		$this->namedtag->putBoolean("Sheared", $this->isSheared());
		$this->namedtag->putByte("Color", $this->getColor());
	}

	public function onInteract(Player $player, ItemItem $item) : bool{
		if($item instanceof Shears){
			if(!$this->isSheared()){
				$this->setSheared(true);
				$item->applyDamage(1);
				$player->getInventory()->setItemInHand($item);

				$this->level->dropItem($this, Item::get(BlockIds::WOOL, $this->getColor()));
				$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_SHEAR);

				return true;
			}
		}elseif($item instanceof Dye){
			if($this->getColor() !== $item->getDamage()){
				$item->pop();
				$player->getInventory()->setItemInHand($item);

				$this->setColor($item->getDamage());


				return true;
			}
		}

		return false;
	}

	public function entityBaseTick($tickDiff = 1, $EnchantL = 0) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);

		if($this->isSheared() && $this->isOnGround()
			&& $this->level->getBlockIdAt($this->x, $this->y - 1, $this->z) === BlockIds::GRASS
			&& mt_rand(0, 1000) === 500
		){
			$this->broadcastEntityEvent(EntityEventPacket::EAT_GRASS_ANIMATION);

			$this->setSheared(false);
			$hasUpdate = true;
		}

		return $hasUpdate;
	}

	public function getColor() : int{
		return $this->getDataProperty(self::DATA_COLOR);
	}

	public function setColor(int $color) : void{
		$this->setDataProperty(self::DATA_COLOR, self::DATA_TYPE_BYTE, $color);
	}

	public function isSheared() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_SHEARED);
	}

	public function setSheared(bool $value) : void{
		$this->setGenericFlag(self::DATA_FLAG_SHEARED, $value);
	}

	public function getName() : string{
		return "Sheep";
	}

	public function spawnTo(Player $player) : void{
		$player->dataPacket(
			AddEntityPacket::create(
				$this->getId(),
				self::NETWORK_ID,
				$this,
				$this->getMotion(),
				$this->yaw,
				$this->pitch,
				$this->dataProperties,
			)
		);

		parent::spawnTo($player);
	}
	public function getDrops() : array{
		return [
			ItemItem::get(BlockIds::WOOL, $this->getColor(), $this->isSheared() ? 0 : 1),
			($this->isOnFire()
				? ItemItem::get(ItemIds::COOKED_MUTTON, 0, rand(1, 3))
				: ItemItem::get(ItemIds::RAW_MUTTON, 0, rand(1, 3)))
		];
	}

	public static function getRandomColor() : int{
		$rand = (new Random())->nextFloat();

		if($rand <= 0.81836){
			return Wool::WHITE;
		}elseif($rand <= (0.81836 + 0.05)){
			return Wool::LIGHT_GRAY;
		}elseif($rand <= (0.81836 + 0.05 + 0.05)){
			return Wool::GRAY;
		}elseif($rand <= (0.81836 + 0.05 + 0.05 + 0.05)){
			return Wool::BLACK;
		}elseif($rand <= (0.81836 + 0.05 + 0.05 + 0.05 + 0.03)){
			return Wool::BROWN;
		}else{
			return Wool::PINK;
		}
	}
}