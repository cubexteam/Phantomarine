<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;
use pocketmine\level\Level;
use pocketmine\level\particle\MobSpellParticle;
use pocketmine\math\RayTraceResult;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\Player;
use function ceil;
use function mt_rand;

class Arrow extends Projectile{
	const NETWORK_ID = 80;

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = "pickup";

	public $width = 0.25;
	public $height = 0.25;

	protected $gravity = 0.05;
	protected $drag = 0.01;

	protected $damage = 2.0;
	protected $collideTicks = 0;
	protected $pickupMode = self::PICKUP_ANY;
	protected $punchKnockback = 0.0;

	protected $potionId;

	protected $bow;
	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $critical = false, Bow $bow = null){
		if(!isset($nbt->Potion)){
			$nbt->Potion = new ShortTag("Potion", 0);
		}
		parent::__construct($level, $nbt, $shootingEntity);
		$this->potionId = $this->namedtag["Potion"];
		$this->setCritical($critical);
		$this->bow = $bow;
	}

	protected function initEntity() : void{
		parent::initEntity();

		if(isset($this->namedtag->{self::TAG_PICKUP})){
			$this->pickupMode = $this->namedtag[self::TAG_PICKUP];
		}else{
			$this->pickupMode = self::PICKUP_ANY;
		}

		if(isset($this->namedtag->life)){
			$this->collideTicks = $this->namedtag["life"];
		}
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->{self::TAG_PICKUP} = new ByteTag(self::TAG_PICKUP, $this->pickupMode);
		$this->namedtag->life = new ShortTag("life", $this->collideTicks);
	}

	public function getBow() : ?Bow{
		return $this->bow;
	}

	public function setBow(?Bow $bow){
		$this->bow = $bow;
	}
	public function isCritical() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CRITICAL);
	}
	public function getPickupMode() : int{
		return $this->pickupMode;
	}
	public function setPickupMode(int $pickupMode) : void{
		$this->pickupMode = $pickupMode;
	}
	public function setCritical(bool $value = true) : void{
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CRITICAL, $value);
	}
	public function getResultDamage() : int{
		$base = (int) ceil($this->motion->length() * parent::getResultDamage());
		if($this->isCritical()){
			return ($base + mt_rand(0, (int) ($base / 2) + 1));
		}else{
			return $base;
		}
	}
	public function getPotionId() : int{
		return $this->potionId;
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$this->setCritical(false);
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BOW_HIT);
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->broadcastEntityEvent(EntityEventPacket::ARROW_SHAKE, 7);
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		parent::onHitEntity($entityHit, $hitResult);
		if($this->punchKnockback > 0){
			$horizontalSpeed = sqrt($this->motion->x ** 2 + $this->motion->z ** 2);
			if($horizontalSpeed > 0){
				$multiplier = $this->punchKnockback * 0.6 / $horizontalSpeed;
				$entityHit->setMotion($entityHit->getMotion()->add($this->motion->x * $multiplier, 0.1, $this->motion->z * $multiplier));
			}
		}
	}

	public function onCollideWithPlayer(Player $player){
		if($this->blockHit === null){
			return;
		}

		$item = ItemItem::get(ItemIds::ARROW, $this->getPotionId());

		$playerInventory = $player->getInventory();

		$add = false;
		if(!$player->server->allowInventoryCheats and !$player->isCreative()){
			if(
				!$player->getFloatingInventory()->canAddItem($item) or
				!$player->getInventory()->canAddItem($item)
			){
				return;
			}
			$add = true;
		}

		$ev = new InventoryPickupArrowEvent($playerInventory, $this);
		if($this->pickupMode === self::PICKUP_NONE or ($this->pickupMode === self::PICKUP_CREATIVE and !$player->isCreative())){
			$ev->setCancelled();
		}

		if($this->getBow() !== null and $this->getBow()->hasEnchantment(Enchantment::TYPE_BOW_INFINITY)){
			$ev->setCancelled();
		}

		$this->server->getPluginManager()->callEvent($ev);

		if($ev->isCancelled()){
			return;
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
	public function getPunchKnockback() : float{
		return $this->punchKnockback;
	}
	public function setPunchKnockback(float $punchKnockback) : void{
		$this->punchKnockback = $punchKnockback;
	}
	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->potionId != 0){
			if(!$this->onGround or ($this->onGround and ($tickDiff % 4) == 0)){
				$color = Potion::getColor($this->potionId - 1);
				$this->level->addParticle(new MobSpellParticle($this->add(
					$this->width / 2 + mt_rand(-100, 100) / 500,
					$this->height / 2 + mt_rand(-100, 100) / 500,
					$this->width / 2 + mt_rand(-100, 100) / 500), $color[0], $color[1], $color[2]));
			}
			$hasUpdate = true;
		}

		if($this->blockHit !== null){
			$this->collideTicks += $tickDiff;
			if($this->collideTicks > 1200){
				$this->flagForDespawn();
				$hasUpdate = true;
			}
		}else{
			$this->collideTicks = 0;
		}

		return $hasUpdate;
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = Arrow::NETWORK_ID;
		$pk->entityRuntimeId = $this->getId();
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
}