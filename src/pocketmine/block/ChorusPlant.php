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

class ChorusPlant extends Transparent{

	protected $id = self::CHORUS_PLANT;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 0.4;
	}
	public function getToolType(){
		return Tool::TYPE_AXE;
	}
	public function getName() : string{
		return "Chorus Plant";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$this->getLevel()->setBlock($this, $this, true, true);
		return true;
	}

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Vector3::SIDE_DOWN)->getId();
		if($down !== 240 and $down !== 121){
            $this->getLevel()->useBreakOn($this);
		}
	}
	public function getDrops(Item $item) : array{
		$drops = [];
        if(mt_rand(0, 1) === 1){
			$drops[] = [Item::CHORUS_FRUIT, 0, 1];
		}
		return $drops;
	}
}