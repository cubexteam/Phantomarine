<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\Vector3;
use pocketmine\Server;


class Mycelium extends Solid{

	protected $id = self::MYCELIUM;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Mycelium";
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	public function ticksRandomly() : bool{
		return true;
	}
	public function getHardness(){
		return 0.6;
	}
	public function getDrops(Item $item) : array{
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::MYCELIUM, 0, 1],
			];
		}else{
			return [
				[Item::DIRT, 0, 1],
			];
		}
	}

	public function onRandomTick() : void{
		$x = mt_rand($this->x - 1, $this->x + 1);
		$y = mt_rand($this->y - 2, $this->y + 2);
		$z = mt_rand($this->z - 1, $this->z + 1);
		$block = $this->getLevel()->getBlockAt($x, $y, $z);
		if($block->getId() === Block::DIRT){
			if($block->getSide(Vector3::SIDE_UP) instanceof Transparent){
				Server::getInstance()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($block, $this, BlockFactory::get(Block::MYCELIUM)));
				if(!$ev->isCancelled()){
					$this->getLevel()->setBlock($block, $ev->getNewState());
				}
			}
		}
	}
}
