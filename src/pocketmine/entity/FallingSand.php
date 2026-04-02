<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\block\Anvil;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\SnowLayer;
use pocketmine\block\utils\Fallable;
use pocketmine\event\entity\EntityBlockChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Position;
use pocketmine\level\sound\AnvilFallSound;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;
use function abs;
use function get_class;

class FallingSand extends Entity{
	const NETWORK_ID = 66;

	public $width = 0.98;
	public $height = 0.98;

	protected $baseOffset = 0.49;

	protected $gravity = 0.04;
	protected $drag = 0.02;
	protected $block;

	public $canCollide = false;

	protected function initEntity(){
		parent::initEntity();

		$blockId = 0;
		$damage = 0;

		if(isset($this->namedtag->TileID)){
			$blockId = (int) $this->namedtag["TileID"];
		}elseif(isset($this->namedtag->Tile)){
			$blockId = (int) $this->namedtag["Tile"];
			$this->namedtag["TileID"] = new IntTag("TileID", $blockId);
		}

		if(isset($this->namedtag->Data)){
			$damage = $this->namedtag["Data"];
		}

		if($blockId === 0){
			throw new \UnexpectedValueException("Invalid " . get_class($this) . " entity: block ID is 0 or missing");
		}

		$this->block = BlockFactory::get($blockId, $damage);

		$this->setDataProperty(self::DATA_VARIANT, self::DATA_TYPE_INT, $this->block->getId() | ($this->block->getDamage() << 8));
	}
	public function canCollideWith(Entity $entity){
		return false;
	}

	public function canBeMovedByCurrents() : bool{
		return false;
	}
	public function attack(EntityDamageEvent $source){
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}
	public function entityBaseTick($tickDiff = 1){
		if($this->closed){
			return false;
		}

		$height = $this->fallDistance;

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->isFlaggedForDespawn()){
			$pos = Position::fromObject($this->add(-$this->width / 2, $this->height, -$this->width / 2)->floor(), $this->getLevel());

			$this->block->position($pos);

			$blockTarget = null;
			if($this->block instanceof Fallable){
				$blockTarget = $this->block->tickFalling();
			}

			if($this->onGround or $blockTarget !== null){
				$this->flagForDespawn();

				$block = $this->level->getBlock($pos);
				if(($block->isTransparent() and !$block->canBeReplaced()) or !$this->level->isInWorld($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()) or ($this->onGround and abs($this->y - $this->getFloorY()) > 0.001)){
					$this->getLevel()->dropItem($this, ItemItem::get($this->getBlock(), $this->getDamage()));
				}else{
					if($block instanceof SnowLayer){
						$oldDamage = $block->getDamage();
						$this->server->getPluginManager()->callEvent($ev = new EntityBlockChangeEvent($this, $block, BlockFactory::get($this->getBlock(), $this->getDamage() + $oldDamage)));
					}else{
						$this->server->getPluginManager()->callEvent($ev = new EntityBlockChangeEvent($this, $block, $blockTarget ?? $this->block));
					}

					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($pos, $ev->getTo(), true);
						if($ev->getTo() instanceof Anvil){
							$sound = new AnvilFallSound($this);
							$this->getLevel()->addSound($sound);
							foreach($this->level->getNearbyEntities($this->boundingBox->expandedCopy(0.1, 0.1, 0.1), $this) as $entity){
								$entity->scheduleUpdate();
								if(!$entity->isAlive()){
									continue;
								}
								if($entity instanceof Living){
									$damage = ($height - 1) * 2;
									if($damage > 40) $damage = 40;
									$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageByEntityEvent::CAUSE_FALL, $damage, 0.1);
									$entity->attack($ev);
								}
							}

						}
					}
				}
				$hasUpdate = true;
			}
		}

		return $hasUpdate;
	}

	public function getBlock(){
		return $this->block->getId();
	}

	public function getDamage(){
		return $this->block->getDamage();
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->TileID = new IntTag("TileID", $this->block->getId());
		$this->namedtag->Data = new ByteTag("Data", $this->block->getDamage());
	}
	public function spawnTo(Player $player){
		$fix = $this->getOffsetPosition($this);

		$pk = new AddEntityPacket();
		$pk->type = FallingSand::NETWORK_ID;
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $fix->x;
		$pk->y = $fix->y;
		$pk->z = $fix->z;
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
