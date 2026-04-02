<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


use pocketmine\block\utils\MinimumCostFlowCalculator;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\sound\FizzSound;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use function lcg_value;

abstract class Liquid extends Transparent{
	public $adjacentSources = 0;
	protected $flowVector = null;
	public function hasEntityCollision(){
		return true;
	}
	public function isBreakable(Item $item){
		return false;
	}
	public function canBeReplaced(){
		return true;
	}

	public function canBeFlowedInto(){
		return true;
	}
	public function isSolid(){
		return false;
	}

	public function getHardness(){
		return 100;
	}

	protected function recalculateBoundingBox(){
		return null;
	}

	abstract public function getStillForm() : Block;

	abstract public function getFlowingForm() : Block;

	abstract public function getBucketFillSound() : int;

	abstract public function getBucketEmptySound() : int;
	public function getFluidHeightPercent(){
		$d = $this->meta;
		if($d >= 8){
			$d = 0;
		}

		return ($d + 1) / 9;
	}
	protected function getFlowDecay(Block $block) : int{
		if($block->getId() !== $this->getId()){
			return -1;
		}

		return $block->getDamage();
	}

	protected function getEffectiveFlowDecay(Block $block) : int{
		if($block->getId() !== $this->getId()){
			return -1;
		}

		$decay = $block->getDamage();

		if($decay >= 8){
			$decay = 0;
		}

		return $decay;
	}

	public function updateState() : void{
		parent::updateState();
		$this->flowVector = null;
	}

	public function getFlowVector() : Vector3{
		if($this->flowVector !== null){
			return $this->flowVector;
		}

		$vector = new Vector3(0, 0, 0);

		$decay = $this->getEffectiveFlowDecay($this);

		$level = $this->level;

		for($j = 0; $j < 4; ++$j){

			$x = $this->x;
			$y = $this->y;
			$z = $this->z;

			if($j === 0){
				--$x;
			}elseif($j === 1){
				++$x;
			}elseif($j === 2){
				--$z;
			}elseif($j === 3){
				++$z;
			}
			$sideBlock = $level->getBlockAt($x, $y, $z);
			$blockDecay = $this->getEffectiveFlowDecay($sideBlock);

			if($blockDecay < 0){
				if(!$sideBlock->canBeFlowedInto()){
					continue;
				}

				$blockDecay = $this->getEffectiveFlowDecay($level->getBlockAt($x, $y - 1, $z));

				if($blockDecay >= 0){
					$realDecay = $blockDecay - ($decay - 8);
					$vector->x += ($sideBlock->x - $this->x) * $realDecay;
					$vector->y += ($sideBlock->y - $this->y) * $realDecay;
					$vector->z += ($sideBlock->z - $this->z) * $realDecay;
				}

				continue;
			}else{
				$realDecay = $blockDecay - $decay;
				$vector->x += ($sideBlock->x - $this->x) * $realDecay;
				$vector->y += ($sideBlock->y - $this->y) * $realDecay;
				$vector->z += ($sideBlock->z - $this->z) * $realDecay;
			}
		}

		if($this->getDamage() >= 8){
			if(
				!$this->canFlowInto($level->getBlockAt($this->x, $this->y, $this->z - 1)) or
				!$this->canFlowInto($level->getBlockAt($this->x, $this->y, $this->z + 1)) or
				!$this->canFlowInto($level->getBlockAt($this->x - 1, $this->y, $this->z)) or
				!$this->canFlowInto($level->getBlockAt($this->x + 1, $this->y, $this->z)) or
				!$this->canFlowInto($level->getBlockAt($this->x, $this->y + 1, $this->z - 1)) or
				!$this->canFlowInto($level->getBlockAt($this->x, $this->y + 1, $this->z + 1)) or
				!$this->canFlowInto($level->getBlockAt($this->x - 1, $this->y + 1, $this->z)) or
				!$this->canFlowInto($level->getBlockAt($this->x + 1, $this->y + 1, $this->z))
			){
				$vector = $vector->normalize()->add(0, -6, 0);
			}
		}

		return $this->flowVector = $vector->normalize();
	}

	public function addVelocityToEntity(Entity $entity) : ?Vector3{
		if($entity->canBeMovedByCurrents()){
			return $this->getFlowVector();
		}
		return null;
	}

	abstract public function tickRate() : int;
	public function getFlowDecayPerBlock() : int{
		return 1;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->checkForHarden()) {
			$this->level->scheduleDelayedBlockUpdate($this, $this->tickRate());
		}
	}

	public function onScheduledUpdate() : void{
		$decay = $this->getFlowDecay($this);
		$multiplier = $this->getFlowDecayPerBlock();

		$level = $this->level;

		if($decay > 0){
			$smallestFlowDecay = -100;
			$this->adjacentSources = 0;
			$smallestFlowDecay = $this->getSmallestFlowDecay($level->getBlockAt($this->x, $this->y, $this->z - 1), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($level->getBlockAt($this->x, $this->y, $this->z + 1), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($level->getBlockAt($this->x - 1, $this->y, $this->z), $smallestFlowDecay);
			$smallestFlowDecay = $this->getSmallestFlowDecay($level->getBlockAt($this->x + 1, $this->y, $this->z), $smallestFlowDecay);

			$newDecay = $smallestFlowDecay + $multiplier;

			if($newDecay >= 8 or $smallestFlowDecay < 0){
				$newDecay = -1;
			}

			if(($topFlowDecay = $this->getFlowDecay($level->getBlockAt($this->x, $this->y + 1, $this->z))) >= 0){
				$newDecay = $topFlowDecay | 0x08;
			}

			if($this->adjacentSources >= 2 and $this instanceof Water){
				$bottomBlock = $level->getBlockAt($this->x, $this->y - 1, $this->z);
				if($bottomBlock->isSolid()){
					$newDecay = 0;
				}elseif($bottomBlock instanceof Water and $bottomBlock->getDamage() === 0){
					$newDecay = 0;
				}
			}

			if($newDecay !== $decay){
				$decay = $newDecay;
				if($decay < 0){
					$level->setBlock($this, BlockFactory::get(Block::AIR), true, true);
				}else{
					$level->setBlock($this, BlockFactory::get($this->id, $decay), true, true);
					$level->scheduleDelayedBlockUpdate($this, $this->tickRate());
				}
			}
		}

		if($decay >= 0){
			$bottomBlock = $level->getBlockAt($this->x, $this->y - 1, $this->z);

			$this->flowIntoBlock($bottomBlock, $decay | 0x08);

			if($decay === 0 or !$bottomBlock->canBeFlowedInto()){
				if($decay >= 8){
					$adjacentDecay = 1;
				}else{
					$adjacentDecay = $decay + $multiplier;
				}

				if($adjacentDecay < 8){
                    $calculator = new MinimumCostFlowCalculator($this->getLevel(), $this->getFlowDecayPerBlock(), \Closure::fromCallable([$this, 'canFlowInto']));
                    foreach($calculator->getOptimalFlowDirections($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) as $facing){
                        $this->flowIntoBlock($level->getBlock($this->getSide($facing)), $adjacentDecay);
                    }
				}
			}

			$this->checkForHarden();
		}
	}
	protected function flowIntoBlock(Block $block, int $newFlowDecay) : void{
		if($this->canFlowInto($block) and !($block instanceof Liquid)) {
			$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, BlockFactory::get($this->getId(), $newFlowDecay)));
			if (!$ev->isCancelled()) {
				if ($block instanceof Lava) {
					$this->triggerLavaMixEffects($block);
				} elseif ($block->getId() > 0) {
					$this->level->useBreakOn($block);
				}

				$this->level->setBlock($block, BlockFactory::get($this->getId(), $newFlowDecay), true, true);
				$this->level->scheduleDelayedBlockUpdate($block, $this->tickRate());
			}
		}
	}

	private function getSmallestFlowDecay(Block $block, int $decay) : int{
		$blockDecay = $this->getFlowDecay($block);

		if($blockDecay < 0){
			return $decay;
		}elseif($blockDecay === 0){
			++$this->adjacentSources;
		}elseif($blockDecay >= 8){
			$blockDecay = 0;
		}

		return ($decay >= 0 && $blockDecay >= $decay) ? $decay : $blockDecay;
	}

	protected function checkForHarden() : bool{
		return false;
	}

	protected function liquidCollide(Block $cause, Block $result) : bool{
		$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockFormEvent($this, $result, $cause));
		if (!$ev->isCancelled()) {
			$this->level->setBlock($this, $result, true, true);
			$this->level->broadcastLevelSoundEvent($this->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_FIZZ, (int)((2.6 + (lcg_value() - lcg_value()) * 0.8) * 1000));
		}
		return true;
	}

	protected function canFlowInto(Block $block) : bool{
		return $this->level->isInWorld($block->x, $block->y, $block->z) and $block->canBeFlowedInto() and !($block instanceof Liquid and $block->meta === 0);
	}
	public function getDrops(Item $item) : array{
		return [];
	}
	protected function triggerLavaMixEffects(Vector3 $pos){
		$this->level->addSound(new FizzSound($pos->add(0.5, 0.5, 0.5), 2.5 + mt_rand(0, 1000) / 1000 * 0.8));

		for($i = 0; $i < 8; ++$i){
			$this->level->addParticle(new SmokeParticle($pos->add(mt_rand(0, 80) / 100, 0.5, mt_rand(0, 80) / 100)));
		}
	}
}
