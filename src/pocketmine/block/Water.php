<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class Water extends Liquid{

	protected $id = self::WATER;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Water";
	}

	public function getLightFilter() : int{
		return 2;
	}

	public function tickRate() : int{
		return 5;
	}

	public function getStillForm() : Block{
		return BlockFactory::get(Block::STILL_WATER, $this->meta);
	}

	public function getFlowingForm() : Block{
		return BlockFactory::get(Block::WATER, $this->meta);
	}

	public function getBucketFillSound() : int{
		return LevelSoundEventPacket::SOUND_BUCKET_FILL_WATER;
	}

	public function getBucketEmptySound() : int{
		return LevelSoundEventPacket::SOUND_BUCKET_EMPTY_WATER;
	}
	public function onEntityInside(Entity $entity) : bool{
		$entity->resetFallDistance();
		if($entity->fireTicks > 0){
			$entity->extinguish();
		}
		return true;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$ret = $this->getLevel()->setBlock($this, $this, true, false);
		$this->getLevel()->scheduleDelayedBlockUpdate($this, $this->tickRate());

		return $ret;
	}
}
