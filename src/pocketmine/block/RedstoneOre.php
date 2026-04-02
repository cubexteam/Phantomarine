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

class RedstoneOre extends Solid{

	protected $id = self::REDSTONE_ORE;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Redstone Ore";
	}
	public function getHardness(){
		return 3;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		return $this->getLevel()->setBlock($this, BlockFactory::get(Block::GLOWING_REDSTONE_ORE, $this->meta));
	}

	public function onNearbyBlockChange() : void{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::GLOWING_REDSTONE_ORE, $this->meta));
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= Tool::TIER_IRON){
			if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
				return [
					[Item::REDSTONE_ORE, 0, 1],
				];
			}else{
				$fortuneL = $item->getEnchantmentLevel(Enchantment::TYPE_MINING_FORTUNE);
				$fortuneL = $fortuneL > 3 ? 3 : $fortuneL;
				return [
					[Item::REDSTONE_DUST, 0, mt_rand(4, 5 + $fortuneL)],
				];
			}
		}else{
			return [];
		}
	}
}
