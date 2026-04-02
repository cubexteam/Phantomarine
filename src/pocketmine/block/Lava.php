<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;

class Lava extends Liquid{

	protected $id = self::LAVA;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getLightLevel(){
		return 15;
	}
	public function getName() : string{
		return "Lava";
	}

	public function tickRate() : int{
		return 30;
	}

	public function getFlowDecayPerBlock() : int{
		return 2;
	}

	protected function checkForHarden() : bool{
		$colliding = null;
		foreach(Facing::ALL as $side){
			if($side === Facing::DOWN){
				continue;
			}
			$blockSide = $this->getSide($side);
			if($blockSide instanceof Water){
				$colliding = $blockSide;
				break;
			}
		}

		if($colliding !== null){
			if($this->getDamage() === 0){
				$this->liquidCollide($colliding, BlockFactory::get(Block::OBSIDIAN));
				return true;
			}elseif($this->getDamage() <= 4){
				$this->liquidCollide($colliding, BlockFactory::get(Block::COBBLESTONE));
				return true;
			}
		}
		return false;
	}

	protected function flowIntoBlock(Block $block, int $newFlowDecay) : void{
		if($block instanceof Water){
			$block->liquidCollide($this, BlockFactory::get(Block::STONE));
		}else{
			parent::flowIntoBlock($block, $newFlowDecay);
		}
	}

	public function getStillForm() : Block{
		return BlockFactory::get(Block::STILL_LAVA, $this->meta);
	}

	public function getFlowingForm() : Block{
		return BlockFactory::get(Block::LAVA, $this->meta);
	}

	public function getBucketFillSound() : int{
		return LevelSoundEventPacket::SOUND_BUCKET_FILL_LAVA;
	}

	public function getBucketEmptySound() : int{
		return LevelSoundEventPacket::SOUND_BUCKET_EMPTY_LAVA;
	}
	public function onEntityInside(Entity $entity) : bool{
		$entity->fallDistance *= 0.5;
		$ProtectL = 0;
		if(!$entity->hasEffect(Effect::FIRE_RESISTANCE)){
			$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_LAVA, 4);
			if($entity->attack($ev) === true){
				$ev->useArmors();
			}
			$ProtectL = $ev->getFireProtectL();
		}

		$ev = new EntityCombustByBlockEvent($this, $entity, 15, $ProtectL);
		Server::getInstance()->getPluginManager()->callEvent($ev);
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}

		$entity->resetFallDistance();
		return true;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$ret = $this->getLevel()->setBlock($this, $this, true, false);
		$this->getLevel()->scheduleDelayedBlockUpdate($this, $this->tickRate());

		return $ret;
	}

}
