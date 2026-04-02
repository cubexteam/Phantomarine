<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\Vector3;
use pocketmine\Player;

class SnowLayer extends Flowable{

	protected $id = self::SNOW_LAYER;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Snow Layer";
	}
	public function canBeReplaced(){
		return $this->meta < 7;
	}
	public function getHardness(){
		return 0.1;
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	private function canBeSupportedBy(Block $b) : bool{
		return $b->isSolid() or ($b->getId() === $this->getId() and $b->getDamage() === 7);
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($block->getId() === $this->getId() and $block->getDamage() < 7){
			$this->setDamage($block->getDamage() + 1);
		}

		if($this->canBeSupportedBy($block->getSide(Vector3::SIDE_DOWN))){
			$this->getLevel()->setBlock($block, $this, true);

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Vector3::SIDE_DOWN)->isSolid()){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), false, false);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->level->getBlockLightAt($this->x, $this->y, $this->z) >= 12){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), false, false);
		}
	}
	public function getDrops(Item $item) : array{
		if($item->isShovel() !== false){
			return [
				[Item::SNOWBALL, 0, 1],
			];
		}

		return [];
	}
}