<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Server;

class PumpkinStem extends Crops{

	protected $id = self::PUMPKIN_STEM;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Pumpkin Stem";
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
					$this->getLevel()->setBlock($this, $ev->getNewState(), true);
				}
			}else{
				for($side = 2; $side <= 5; ++$side){
					$b = $this->getSide($side);
					if($b->getId() === self::PUMPKIN){
						return;
					}
				}
				$side = $this->getSide(mt_rand(2, 5));
				$d = $side->getSide(Vector3::SIDE_DOWN);
				if($side->getId() === self::AIR and ($d->getId() === self::FARMLAND or $d->getId() === self::GRASS or $d->getId() === self::DIRT)){
					Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($side, BlockFactory::get(Block::PUMPKIN)));
					if(!$ev->isCancelled()){
						$this->getLevel()->setBlock($side, $ev->getNewState(), true);
					}
				}
			}
		}
	}
	public function getDrops(Item $item) : array{
		return [
			[Item::PUMPKIN_SEEDS, 0, mt_rand(0, 2)],
		];
	}
}