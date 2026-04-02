<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\Player;

class Ice extends Transparent{

	protected $id = self::ICE;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Ice";
	}
	public function getHardness(){
		return 0.5;
	}

	public function getLightFilter() : int{
		return 2;
	}

	public function getFrictionFactor(){
		return 0.98;
	}

	public function ticksRandomly() : bool{
		return true;
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) === 0){
			return $this->getLevel()->setBlock($this, BlockFactory::get(Block::WATER), true);
		}
		return true;
	}

	public function onRandomTick() : void{
		if($this->level->getHighestAdjacentBlockLight($this->x, $this->y, $this->z) >= 12){
			$this->level->useBreakOn($this);
		}
	}
	public function getDrops(Item $item) : array{
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::ICE, 0, 1],
			];
		}else{
			return [];
		}
	}
}
