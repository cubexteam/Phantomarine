<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Potion;
use pocketmine\level\Level;
use pocketmine\level\particle\SpellParticle;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class ThrownPotion extends Throwable{
	const NETWORK_ID = 86;

	const DATA_POTION_ID = 37;

	public $width = 0.25;
	public $height = 0.25;

	protected $gravity = 0.1;
	protected $drag = 0.05;
	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
		if(!isset($nbt->PotionId)){
			$nbt->PotionId = new ShortTag("PotionId", Potion::AWKWARD);
		}

		parent::__construct($level, $nbt, $shootingEntity);

		unset($this->dataProperties[self::DATA_SHOOTER_ID]);
		$this->setDataProperty(self::DATA_POTION_ID, self::DATA_TYPE_SHORT, $this->getPotionId());
	}
	public function getPotionId() : int{
		return (int) $this->namedtag["PotionId"];
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$effects = Potion::getEffectsById($this->getPotionId());
		$hasEffects = true;

		if(count($effects) === 0){
			$particle = new SpellParticle($this->add(0, 0.01), 0x38, 0x5d, 0xc6);
			$hasEffects = false;
		}else{
			$color = Potion::getColor($this->getPotionId());
			$particle = new SpellParticle($this->add(0, 0.01), $color[0], $color[1], $color[2]);
		}

		$this->getLevel()->addParticle($particle);
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_GLASS);

		if($hasEffects){
			foreach($this->getLevel()->getCollidingEntities($this->boundingBox->expandedCopy(4.125, 2.125, 4.125), $this) as $entity){
				if($entity instanceof Living and $entity->isAlive()){
					$distanceSquared = $entity->getPosition()->add(0, $entity->getEyeHeight())->distanceSquared($this);
					if($distanceSquared > 16){
						continue;
					}

					$distanceMultiplier = 1 - (sqrt($distanceSquared) / 4);
					if($event instanceof ProjectileHitEntityEvent && $entity === $event->getEntityHit()){
						$distanceMultiplier = 1.0;
					}

					foreach($effects as $effect){
						$effect->setDuration($effect->getDuration() * 0.75 * $distanceMultiplier);
						$entity->addEffect($effect);
					}
				}
			}
		}elseif($event instanceof ProjectileHitBlockEvent && in_array($this->getPotionId(), [0, 1, 2, 3, 4])){
			$blockIn = $event->getBlockHit()->getSide($event->getRayTraceResult()->getHitFace());

			if($blockIn->getId() === BlockIds::FIRE){
				$this->getLevel()->setBlock($blockIn->asVector3(), BlockFactory::get(BlockIds::AIR));
			}

			foreach($blockIn->getHorizontalSides() as $horizontalSide){
				if($horizontalSide->getId() === BlockIds::FIRE){
					$this->getLevel()->setBlock($horizontalSide->asVector3(), BlockFactory::get(BlockIds::AIR));
				}
			}
		}
	}
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->type = ThrownPotion::NETWORK_ID;
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motion->x;
		$pk->speedY = $this->motion->y;
		$pk->speedZ = $this->motion->z;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}
