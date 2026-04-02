<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\Player;

class Item extends Entity{
	const NETWORK_ID = 64;

	public const MERGE_CHECK_PERIOD = 2;
	public const DEFAULT_DESPAWN_DELAY = 6000;
	public const NEVER_DESPAWN = -1;
	public const MAX_DESPAWN_DELAY = 32767 + self::DEFAULT_DESPAWN_DELAY;
	protected $owner = "";
	protected $thrower = "";
	protected $pickupDelay = 0;
	protected $item;

	public $width = 0.25;
	public $height = 0.25;
	protected $baseOffset = 0.125;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	public $canCollide = false;
	protected $despawnDelay = self::DEFAULT_DESPAWN_DELAY;

	protected function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(5);
		$this->setHealth((int) $this->namedtag["Health"]);

		if(isset($this->namedtag->Age)){
			$age = $this->namedtag["Age"];
		}else{
			$age = 0;
		}
		if($age === -32768){
			$this->despawnDelay = self::NEVER_DESPAWN;
		}else{
			$this->despawnDelay = max(0, self::DEFAULT_DESPAWN_DELAY - $age);
		}

		if(isset($this->namedtag->PickupDelay)){
			$this->pickupDelay = $this->namedtag["PickupDelay"];
		}
		if(isset($this->namedtag->Owner)){
			$this->owner = $this->namedtag["Owner"];
		}
		if(isset($this->namedtag->Thrower)){
			$this->thrower = $this->namedtag["Thrower"];
		}
		if(!isset($this->namedtag->Item)){
			throw new \UnexpectedValueException("Invalid " . get_class($this) . " entity: expected \"Item\" NBT tag not found");
		}

		assert($this->namedtag->Item instanceof CompoundTag);

		$this->item = ItemItem::nbtDeserialize($this->namedtag->Item);
		if($this->item->isNull()){
			throw new \UnexpectedValueException("Item for " . get_class($this) . " is invalid");
		}
	}

	protected function onFirstUpdate(int $currentTick) : void{
		$this->server->getPluginManager()->callEvent(new ItemSpawnEvent($this));
		parent::onFirstUpdate($currentTick);
	}
	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->isFlaggedForDespawn() and $this->pickupDelay !== self::NEVER_DESPAWN){
			$this->pickupDelay -= $tickDiff;
			if($this->pickupDelay < 0){
				$this->pickupDelay = 0;
			}
			if($this->hasMovementUpdate() && $this->isMergeCandidate() && $this->despawnDelay % self::MERGE_CHECK_PERIOD === 0){
				$mergeable = [$this];
				$mergeTarget = $this;
				foreach($this->getLevel()->getNearbyEntities($this->boundingBox->expandedCopy(0.5, 0.5, 0.5), $this) as $entity){
					if(!$entity instanceof Item or $entity->isFlaggedForDespawn()){
						continue;
					}

					if($entity->isMergeable($this)){
						$mergeable[] = $entity;
						if($entity->item->getCount() > $mergeTarget->item->getCount()){
							$mergeTarget = $entity;
						}
					}
				}
				foreach($mergeable as $itemEntity){
					if($itemEntity !== $mergeTarget){
						$itemEntity->tryMergeInto($mergeTarget);
					}
				}
			}

			$this->despawnDelay -= $tickDiff;
			if($this->despawnDelay <= 0){
				$this->server->getPluginManager()->callEvent($ev = new ItemDespawnEvent($this));
				if($ev->isCancelled()){
					$this->despawnDelay = self::DEFAULT_DESPAWN_DELAY;
				}else{
					$this->flagForDespawn();
					$hasUpdate = true;
				}
			}
		}

		return $hasUpdate;
	}

	private function isMergeCandidate() : bool{
		return $this->pickupDelay !== self::NEVER_DESPAWN && $this->item->getCount() < $this->item->getMaxStackSize();
	}
	public function isMergeable(Item $entity) : bool{
		if(!$this->isMergeCandidate() || !$entity->isMergeCandidate()){
			return false;
		}
		$item = $entity->item;
		return $entity !== $this && $item->canStackWith($this->item) && $item->getCount() + $this->item->getCount() <= $item->getMaxStackSize();
	}
	public function tryMergeInto(Item $consumer) : bool{
		if(!$this->isMergeable($consumer)){
			return false;
		}

		/*$ev = new ItemMergeEvent($this, $consumer);
		$ev->call();

		if($ev->isCancelled()){
			return false;
		}*/

		$consumer->setStackSize($consumer->item->getCount() + $this->item->getCount());
		$this->flagForDespawn();
		$consumer->pickupDelay = max($consumer->pickupDelay, $this->pickupDelay);
		$consumer->despawnDelay = max($consumer->despawnDelay, $this->despawnDelay);

		return true;
	}

	protected function tryChangeMovement(){
		$this->checkObstruction($this->x, $this->y, $this->z);
		parent::tryChangeMovement();
	}

	public function setStackSize(int $newCount) : void{
		if($newCount <= 0){
			throw new \InvalidArgumentException("Stack size must be at least 1");
		}
		$this->item->setCount($newCount);
	}

	protected function applyDragBeforeGravity() : bool{
		return true;
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->Item = $this->item->nbtSerialize(-1, "Item");
		$this->namedtag->Health = new ShortTag("Health", (int) $this->getHealth());
		if($this->despawnDelay === self::NEVER_DESPAWN){
			$age = -32768;
		}else{
			$age = self::DEFAULT_DESPAWN_DELAY - $this->despawnDelay;
		}
		$this->namedtag->Age = new ShortTag("Age", $age);
		$this->namedtag->PickupDelay = new ShortTag("PickupDelay", $this->pickupDelay);
		if($this->owner !== null){
			$this->namedtag->Owner = new StringTag("Owner", $this->owner);
		}
		if($this->thrower !== null){
			$this->namedtag->Thrower = new StringTag("Thrower", $this->thrower);
		}
	}
	public function getItem(){
		return $this->item;
	}
	public function canCollideWith(Entity $entity){
		return false;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}
	public function getPickupDelay(){
		return $this->pickupDelay;
	}
	public function setPickupDelay($delay){
		$this->pickupDelay = $delay;
	}
	public function getDespawnDelay() : int{
		return $this->despawnDelay;
	}
	public function setDespawnDelay(int $despawnDelay) : void{
		if(($despawnDelay < 0 or $despawnDelay > self::MAX_DESPAWN_DELAY) and $despawnDelay !== self::NEVER_DESPAWN){
			throw new \InvalidArgumentException("Despawn ticker must be in range 0 ... " . self::MAX_DESPAWN_DELAY . " or " . self::NEVER_DESPAWN . ", got $despawnDelay");
		}
		$this->despawnDelay = $despawnDelay;
	}
	public function getOwner(){
		return $this->owner;
	}
	public function setOwner($owner){
		$this->owner = $owner;
	}
	public function getThrower(){
		return $this->thrower;
	}
	public function setThrower($thrower){
		$this->thrower = $thrower;
	}
	public function spawnTo(Player $player){
		$pk = new AddItemEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motion->x;
		$pk->speedY = $this->motion->y;
		$pk->speedZ = $this->motion->z;
		$pk->item = $this->getItem();
		$player->dataPacket($pk);

		$this->sendData($player);

		parent::spawnTo($player);
	}

	public function onCollideWithPlayer(Player $player){
		if($this->getPickupDelay() !== 0){
			return;
		}

		$item = $this->getItem();
		$playerInventory = $player->getInventory();

		$add = false;
		if(!$player->server->allowInventoryCheats and !$player->isCreative()){
			if(
				!$player->getFloatingInventory()->canAddItem($item) or
				!$playerInventory->canAddItem($item)
			){
				return;
			}
			$add = true;
		}

		$this->server->getPluginManager()->callEvent($ev = new InventoryPickupItemEvent($playerInventory, $this));
		if($ev->isCancelled()){
			return;
		}

		switch($item->getId()){
			case ItemItem::WOOD:
				$player->awardAchievement("mineWood");
				break;
			case ItemItem::DIAMOND:
				$player->awardAchievement("diamond");
				break;
		}

		$pk = new TakeItemEntityPacket();
		$pk->entityRuntimeId = $player->getId();
		$pk->target = $this->getId();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		if($add){
			$player->getFloatingInventory()->addItem(clone $item);
		}

		$this->flagForDespawn();
	}
}
