<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class ChorusFlower extends Transparent{

	protected $id = self::CHORUS_FLOWER;
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
		return "Chorus Flower";
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === 240 or $down->getId() === 121 or $down->getId() === 200){
			$block0 = $this->getSide(Vector3::SIDE_NORTH);
			$block1 = $this->getSide(Vector3::SIDE_SOUTH);
			$block2 = $this->getSide(Vector3::SIDE_WEST);
			$block3 = $this->getSide(Vector3::SIDE_EAST);
			if(!$block0->isSolid() and !$block1->isSolid() and !$block2->isSolid() and !$block3->isSolid()){
				$this->getLevel()->setBlock($this, $this, true);
				return true;
			}
		}

		return false;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(mt_rand(1, 2) == 2){
			return;
		}

		$count = 0;
		for($i = 0; $i <= 15; $i++){
			if($this->getLevel()->getBlockAt($this->getX(), $this->getY() - $i, $this->getZ())->getId() == 240){
				$count++;
			}
		}
		if($count >= 10){
			return;
		}

		if($this->getSide(Vector3::SIDE_DOWN)->getId() == 121 or $this->getSide(Vector3::SIDE_DOWN)->getId() == 240){
			Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($this->getLevel()->getBlock($this), new ChorusFlower()));
			if($ev->isCancelled()){
				return;
			}

			$rand = mt_rand(1, 3);
			for($i = 0; $i <= $rand; $i++){
				if($this->getLevel()->getBlockAt($this->getX(), $this->getY() + $i, $this->getZ())->getId() !== 0 and $this->getLevel()->getBlockAt($this->getX(), $this->getY() + $i, $this->getZ())->getId() !== 200 and $this->getLevel()->getBlockAt($this->getX(), $this->getY() + $i, $this->getZ())->getId() !== 240){
					return;
				}

				$this->getLevel()->setBlock(new Vector3($this->getX(), $this->getY() + $i, $this->getZ()), BlockFactory::get(240, 0));
			}

			$this->getLevel()->setBlock(new Vector3($this->getX(), $this->getY() + $rand, $this->getZ()), BlockFactory::get(200, 0));
		}else{
			$this->getLevel()->useBreakOn($this);
		}
	}
	public function getDrops(Item $item) : array{
		$drops = [];
			$drops[] = [Item::CHORUS_FRUIT, 0, 1];
		return $drops;
	}

}