<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Arrow;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Server;
use function intdiv;
use function max;
use function min;
use function mt_rand;

class Fire extends Flowable{

	protected $id = self::FIRE;
	private $temporalVector = null;
	public function __construct($meta = 0){
		$this->meta = $meta;
		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}
	}
	public function hasEntityCollision(){
		return true;
	}
	public function getName() : string{
		return "Fire Block";
	}
	public function getLightLevel(){
		return 15;
	}
	public function isBreakable(Item $item){
		return false;
	}
	public function canBeReplaced(){
		return true;
	}
	public function onEntityInside(Entity $entity) : bool{
		$ProtectL = 0;
		if(!$entity->hasEffect(Effect::FIRE_RESISTANCE)){
			$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1);
			if($entity->attack($ev) === true){
				$ev->useArmors();
			}
			$ProtectL = $ev->getFireProtectL();
		}

		$ev = new EntityCombustByBlockEvent($this, $entity, 8, $ProtectL);
		if($entity instanceof Arrow){
			$ev->setCancelled();
		}
		Server::getInstance()->getPluginManager()->callEvent($ev);
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}
		return true;
	}
	public function getDrops(Item $item) : array{
		return [];
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent() and !$this->hasAdjacentFlammableBlocks()){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);
		}else{
			$this->level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$down = $this->getSide(Facing::DOWN);

		$result = null;
		if($this->meta < 15 and mt_rand(0, 2) === 0){
			$this->meta++;
			$result = $this;
		}
		$canSpread = true;

		if(!$down->burnsForever()){
			if($this->meta === 15){
				if(!$down->isFlammable() and mt_rand(0, 3) === 3){
					$canSpread = false;
					$result = BlockFactory::get(Block::AIR);
				}
			}elseif(!$this->hasAdjacentFlammableBlocks()){
				$canSpread = false;
				if($down->isTransparent() or $this->meta > 3){
					$result = BlockFactory::get(Block::AIR);
				}
			}
		}

		if($result !== null){
			$this->level->setBlock($this, $result);
		}

		$this->level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));

		if($canSpread){
			$this->burnBlocksAround();
			$this->spreadFire();
		}
	}

	public function onScheduledUpdate() : void{
		$this->onRandomTick();
	}

	private function hasAdjacentFlammableBlocks() : bool{
		foreach(Facing::ALL as $face){
			if($this->getSide($face)->isFlammable()){
				return true;
			}
		}

		return false;
	}

	private function burnBlocksAround() : void{

		foreach($this->getHorizontalSides() as $side){
			$this->burnBlock($side, 300);
		}

		$this->burnBlock($this->getSide(Facing::UP), 350);
		$this->burnBlock($this->getSide(Facing::DOWN), 350);
	}

	private function burnBlock(Block $block, int $chanceBound) : void{
		if(mt_rand(0, $chanceBound) < $block->getBurnAbility()){
			$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockBurnEvent($block, $this));
			if(!$ev->isCancelled()){
				$block->onIncinerate();

				$spreadedFire = false;
				if(mt_rand(0, $this->meta + 9) < 5){
					$spreadedFire = $this->spreadBlock($block, BlockFactory::get(Block::FIRE, min(15, $this->meta + (mt_rand(0, 4) >> 2))));
				}
				if(!$spreadedFire){
					$this->level->setBlock($block, BlockFactory::get(Block::AIR));
				}

				if($this->level->getBlock($block)->isSameState($block)){
					$spreadedFire = false;
					if(mt_rand(0, $this->meta + 9) < 5){
						$fire = clone $this;
						$fire->meta = min(15, $fire->meta + (mt_rand(0, 4) >> 2));
						$spreadedFire = $this->spreadBlock($block, $fire);
					}
					if(!$spreadedFire){
						$this->level->setBlock($block, BlockFactory::get(Block::AIR));
					}
				}
			}
		}
	}

	private function spreadFire() : void{
		$world = $this->getLevel();
		$difficultyChanceIncrease = $world->getDifficulty() * 7;
		$ageDivisor = $this->meta + 30;

		for($y = -1; $y <= 4; ++$y){
			$targetY = $y + (int) $this->y;
			if($targetY < 0 || $targetY >= Level::Y_MAX){
				continue;
			}
			$randomBound = 100 + ($y > 1 ? ($y - 1) * 100 : 0);

			for($z = -1; $z <= 1; ++$z){
				$targetZ = $z + (int) $this->z;
				for($x = -1; $x <= 1; ++$x){
					if($x === 0 and $y === 0 and $z === 0){
						continue;
					}
					$targetX = $x + (int) $this->x;
					if(!$world->isInWorld($targetX, $targetY, $targetZ)){
						continue;
					}

					if(!$world->isChunkLoaded($targetX >> Chunk::COORD_BIT_SIZE, $targetZ >> Chunk::COORD_BIT_SIZE)){
						continue;
					}
					$block = $world->getBlockAt($targetX, $targetY, $targetZ);
					if($block->getId() !== BlockIds::AIR){
						continue;
					}


					$encouragement = 0;
					foreach($block->sides() as $vector3){
						if($world->isInWorld($vector3->x, $vector3->y, $vector3->z)){
							$encouragement = max($encouragement, $world->getBlockAt($vector3->x, $vector3->y, $vector3->z)->getBurnChance());
						}
					}

					if($encouragement <= 0){
						continue;
					}

					$maxChance = intdiv($encouragement + 40 + $difficultyChanceIncrease, $ageDivisor);

					if($maxChance > 0 and mt_rand(0, $randomBound - 1) <= $maxChance){
						$new = clone $this;
						$new->meta = min(15, $this->meta + (mt_rand(0, 4) >> 2));
						$this->spreadBlock($block, $new);
					}
				}
			}
		}
	}

	private function spreadBlock(Block $block, Block $newState) : bool{
		$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, $newState));
		if(!$ev->isCancelled()){
			$block->getLevel()->setBlock($block, $ev->getNewState());
			return true;
		}

		return false;
	}
}
