<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

class NetherWart extends Flowable{

	protected $id = self::NETHER_WART_BLOCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Nether Wart Block";
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === self::SOUL_SAND){
			$this->getLevel()->setBlock($block, $this, true, true);
			return true;
		}
		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->getId() !== Block::SOUL_SAND){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->meta < 3 and mt_rand(0, 10) === 0){
			$block = clone $this;
			$block->meta++;
			$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this, $block));

			if(!$ev->isCancelled()){
				$this->getLevel()->setBlock($this, $ev->getNewState(), false, true);
			}
		}
	}
	public function getDrops(Item $item) : array{
		$drops = [];
		if($this->meta >= 0x03){
			$fortunel = $item->getEnchantmentLevel(Enchantment::TYPE_MINING_FORTUNE);
			$fortunel = $fortunel > 3 ? 3 : $fortunel;
			$drops[] = [Item::NETHER_WART, 0, mt_rand(2, 4 + $fortunel)];
		}else{
			$drops[] = [Item::NETHER_WART, 0, 1];
		}
		return $drops;
	}
}
