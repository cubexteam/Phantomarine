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

class TallGrass extends Flowable{

	const NORMAL = 1;
	const FERN = 2;

	protected $id = self::TALL_GRASS;
	public function __construct($meta = 1){
		$this->meta = $meta;
	}
	public function canBeReplaced(){
		return true;
	}
	public function getName() : string{
		static $names = [
			0 => "Dead Shrub",
			1 => "Tall Grass",
			2 => "Fern"
		];
		return $names[$this->meta & 0x03] ?? "Unknown";
	}
	public function getBurnChance() : int{
		return 60;
	}
	public function getBurnAbility() : int{
		return 100;
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN)->getId();
		if($down === self::GRASS or $down === self::DIRT){
			$this->getLevel()->setBlock($block, $this, true);

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->isTransparent()){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, true);
		}
	}
	public function getToolType(){
		return Tool::TYPE_SHEARS;
	}
	public function getDrops(Item $item) : array{
		if(mt_rand(0, 15) === 0){
			return [
				[Item::WHEAT_SEEDS, 0, 1]
			];
		}

		return [];
	}

}
