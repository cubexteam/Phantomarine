<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

abstract class Crops extends Flowable{
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($block->getSide(Vector3::SIDE_DOWN)->getId() === Block::FARMLAND){
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}
	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){
			$block = clone $this;
			$block->meta += mt_rand(2, 5);
			if($block->meta > 7){
				$block->meta = 7;
			}

			Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this, $block));

			if(!$ev->isCancelled()){
				$this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
			}

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->getId() !== Block::FARMLAND){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(mt_rand(0, 2) === 1){
			if($this->meta < 0x07){
				$block = clone $this;
				++$block->meta;
				Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this, $block));

				if(!$ev->isCancelled()){
					$this->getLevel()->setBlock($this, $ev->getNewState(), true, true);
				}
			}
		}
	}
}