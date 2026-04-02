<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;


use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class GrassPath extends Transparent{

	protected $id = self::GRASS_PATH;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Grass Path";
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 1,
			$this->z + 1
		);
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_UP)->isSolid()){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::DIRT), true);
		}
	}
	public function getHardness(){
		return 0.6;
	}
	public function getDrops(Item $item) : array{
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::GRASS_PATH, 0, 1],
			];
		}else{
			return [
				[Item::DIRT, 0, 1],
			];
		}
	}
}
