<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;


use pocketmine\block\Block;
use pocketmine\event\entity\EntityCombustByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Timings;
use pocketmine\item\Potion;
use pocketmine\level\Level;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\IntTag;

abstract class Projectile extends Entity{

	const DATA_SHOOTER_ID = 17;
	protected $damage = 0.0;
	protected $blockHit;
	protected $blockHitId;
	protected $blockHitData;
	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($level, $nbt);
		if($shootingEntity !== null){
			$this->setOwningEntity($shootingEntity);
		}
	}
	public function attack(EntityDamageEvent $source){
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	protected function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(1);
		$this->setHealth(1);

		if(isset($this->namedtag->damage)){
			$this->damage = $this->namedtag["damage"];
		}

		do{
			$blockHit = null;
			$blockId = null;
			$blockData = null;

			if(isset($this->namedtag->tileX) and isset($this->namedtag->tileY) and isset($this->namedtag->tileZ)){
				$blockHit = new Vector3($this->namedtag["tileX"], $this->namedtag["tileY"], $this->namedtag["tileZ"]);
			}else{
				break;
			}

			if(isset($this->namedtag->blockId)){
				$blockId = $this->namedtag["blockId"];
			}else{
				break;
			}

			if(isset($this->namedtag->blockData)){
				$blockData = $this->namedtag["blockData"];
			}else{
				break;
			}

			$this->blockHit = $blockHit;
			$this->blockHitId = $blockId;
			$this->blockHitData = $blockData;
		}while(false);
	}
	public function canCollideWith(Entity $entity){
		return ($entity instanceof Living || $entity instanceof EnderCrystal) && !$this->onGround;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}
	public function getResultDamage() : int{
		return (int) ceil($this->damage);
	}
	public function getBaseDamage() : float{
		return $this->damage;
	}
	public function setBaseDamage(float $damage) : void{
		$this->damage = $damage;
	}
	protected function onHit(ProjectileHitEvent $event) : void{

	}
	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		$damage = $this->getResultDamage();

		if ($this instanceof ThrownPotion and $entityHit instanceof EnderCrystal){
		}else {
			if ($damage >= 0) {
				if ($this->getOwningEntity() === null) {
					$ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
				} else {
					$ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
				}

				if ($entityHit->attack($ev) === true) {
					if ($this instanceof Arrow and $this->getPotionId() != 0) {
						foreach (Potion::getEffectsById($this->getPotionId() - 1) as $effect) {
							$entityHit->addEffect($effect->setDuration($effect->getDuration() / 8));
						}
					}
					$ev->useArmors();
				}

				if ($this->fireTicks > 0) {
					$ev = new EntityCombustByEntityEvent($this, $entityHit, 5);
					$this->server->getPluginManager()->callEvent($ev);
					if (!$ev->isCancelled()) {
						$entityHit->setOnFire($ev->getDuration());
					}
				}
			}
		}

		$this->flagForDespawn();
	}
	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		$this->blockHit = $blockHit->asVector3();
		$this->blockHitId = $blockHit->getId();
		$this->blockHitData = $blockHit->getDamage();
	}

	public function saveNBT(){
		parent::saveNBT();

		$this->namedtag->damage = new DoubleTag("damage", $this->damage);

		if($this->blockHit !== null){
			$this->namedtag->tileX = new IntTag("tileX", $this->blockHit->x);
			$this->namedtag->tileY = new IntTag("tileY", $this->blockHit->y);
			$this->namedtag->tileZ = new IntTag("tileZ", $this->blockHit->z);

			$this->namedtag->blockId = new IntTag("blockId", $this->blockHitId);
			$this->namedtag->blockData = new ByteTag("blockData", $this->blockHitData);
		}
	}

	protected function applyDragBeforeGravity() : bool{
		return true;
	}

	public function onNearbyBlockChange() : void{
		if($this->blockHit !== null){
			$blockIn = $this->level->getBlockAt($this->blockHit->x, $this->blockHit->y, $this->blockHit->z);
			if($blockIn->getId() !== $this->blockHitId or $blockIn->getDamage() !== $this->blockHitData){
				$this->blockHit = $this->blockHitId = $this->blockHitData = null;
			}
		}

		parent::onNearbyBlockChange();
	}

	public function hasMovementUpdate() : bool{
		return $this->blockHit === null and parent::hasMovementUpdate();
	}

	public function move($dx, $dy, $dz) : void{
		$this->blocksAround = null;

		Timings::$entityMoveTimer->startTiming();

		$start = $this->asVector3();
		$end = $start->add($dx, $dy, $dz);

		$blockHit = null;
		$entityHit = null;
		$hitResult = null;

		foreach(VoxelRayTrace::betweenPoints($start, $end) as $vector3){
			$block = $this->level->getBlockAt($vector3->x, $vector3->y, $vector3->z);

			$blockHitResult = $this->calculateInterceptWithBlock($block, $start, $end);
			if($blockHitResult !== null){
				$end = $blockHitResult->hitVector;
				$blockHit = $block;
				$hitResult = $blockHitResult;
				break;
			}
		}

		$entityDistance = PHP_INT_MAX;

		$newDiff = $end->subtract($start);
		foreach($this->level->getCollidingEntities($this->boundingBox->addCoord($newDiff->x, $newDiff->y, $newDiff->z)->expand(1, 1, 1), $this) as $entity){
			if($entity->getId() === $this->getOwningEntityId() and $this->ticksLived < 5){
				continue;
			}

			$entityBB = $entity->boundingBox->expandedCopy(0.3, 0.3, 0.3);

			$entityHitResult = $entityBB->calculateIntercept($start, $end);

			if($entityHitResult === null){
				continue;
			}

			$distance = $this->distanceSquared($entityHitResult->hitVector);

			if($distance < $entityDistance){
				$entityDistance = $distance;
				$entityHit = $entity;
				$hitResult = $entityHitResult;
				$end = $entityHitResult->hitVector;
			}
		}

		$this->x = $end->x;
		$this->y = $end->y;
		$this->z = $end->z;
		$this->recalculateBoundingBox();

		if($hitResult !== null){
			if($entityHit !== null){
				$ev = new ProjectileHitEntityEvent($this, $hitResult, $entityHit);
			}elseif($blockHit !== null){
				$ev = new ProjectileHitBlockEvent($this, $hitResult, $blockHit);
			}else{
				\assert(false, "unknown hit type");
			}

			$this->server->getPluginManager()->callEvent($ev);
			$this->onHit($ev);

			if($ev instanceof ProjectileHitEntityEvent){
				$this->onHitEntity($ev->getEntityHit(), $ev->getRayTraceResult());
			}else{
				$this->onHitBlock($ev->getBlockHit(), $ev->getRayTraceResult());
			}

			$this->isCollided = $this->onGround = true;
			$this->motion->x = $this->motion->y = $this->motion->z = 0;
		}else{
			$this->isCollided = $this->onGround = false;
			$this->blockHit = $this->blockHitId = $this->blockHitData = null;

			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->yaw = (atan2($this->motion->x, $this->motion->z) * 180 / M_PI);
			$this->pitch = (atan2($this->motion->y, $f) * 180 / M_PI);
		}

		$this->checkChunks();
		$this->checkBlockCollision();

		Timings::$entityMoveTimer->stopTiming();
	}
	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
		return $block->calculateIntercept($start, $end);
	}

}