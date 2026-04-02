<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\block\utils\Fallable;
use pocketmine\block\utils\FallableTrait;
use pocketmine\event\block\BlockFormEvent;
use pocketmine\item\Tool;
use pocketmine\math\Facing;

class ConcretePowder extends Solid implements Fallable{
	use FallableTrait {
		onNearbyBlockChange as protected startFalling;
	}

	protected $id = self::CONCRETE_POWDER;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 0.5;
	}
	public function getResistance(){
		return 2.5;
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}

	public function onNearbyBlockChange() : void{
		if(($water = $this->getAdjacentWater()) !== null){
			$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockFormEvent($this, BlockFactory::get(Block::CONCRETE, $this->meta), $water));
			if (!$ev->isCancelled()){
				$this->level->setBlock($this, $ev->getNewState());
			}
		}else{
			$this->startFalling();
		}
	}

	public function tickFalling() : ?Block{
		if ($this->getAdjacentWater() === null) {
			return null;
		}
		return BlockFactory::get(Block::CONCRETE, $this->meta);
	}

	private function getAdjacentWater() : ?Water{
		foreach(Facing::ALL as $i){
			if($i === Facing::DOWN){
				continue;
			}
			$block = $this->getSide($i);
			if($block instanceof Water) {
				return $block;
			}
		}

		return null;
	}
	public function getName(){
		static $names = [
			0 => "White Concrete Powder",
			1 => "Orange Concrete Powder",
			2 => "Magenta Concrete Powder",
			3 => "Light Blue Concrete Powder",
			4 => "Yellow Concrete Powder",
			5 => "Lime Concrete Powder",
			6 => "Pink Concrete Powder",
			7 => "Gray Concrete Powder",
			8 => "Silver Concrete Powder",
			9 => "Cyan Concrete Powder",
			10 => "Purple Concrete Powder",
			11 => "Blue Concrete Powder",
			12 => "Brown Concrete Powder",
			13 => "Green Concrete Powder",
			14 => "Red Concrete Powder",
			15 => "Black Concrete Powder",
		];
		return $names[$this->meta & 0x0f];
	}

}
