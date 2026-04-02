<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;

class Sugarcane extends Flowable{

	protected $id = self::SUGARCANE_BLOCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Sugarcane";
	}
	public function getDrops(Item $item) : array{
		return [
			[Item::SUGARCANE, 0, 1],
		];
	}
	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){
			if($this->getSide(Vector3::SIDE_DOWN)->getId() !== self::SUGARCANE_BLOCK){
				for($y = 1; $y < 3; ++$y){
					$b = $this->getLevel()->getBlockAt($this->x, $this->y + $y, $this->z);
					if($b->getId() === self::AIR){
						Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($b, BlockFactory::get(Block::SUGARCANE_BLOCK)));
						if($ev->isCancelled()){
							break;
						}
						$this->getLevel()->setBlock($b, $ev->getNewState(), true);
					}else{
						break;
					}
				}
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true);
			}
			if(($player->gamemode & 0x01) === 0){
				$item->pop();
			}

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->isTransparent() and $down->getId() !== self::SUGARCANE_BLOCK){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->getId() !== self::SUGARCANE_BLOCK){
			if($this->meta === 0x0F){
				for($y = 1; $y < 3; ++$y){
					$b = $this->getLevel()->getBlockAt($this->x, $this->y + $y, $this->z);
					if($b->getId() === self::AIR){
						$this->getLevel()->setBlock($b, BlockFactory::get(Block::SUGARCANE_BLOCK), true);
						break;
					}
				}
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true);
			}else{
				++$this->meta;
				$this->getLevel()->setBlock($this, $this, true);
			}
		}
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === self::SUGARCANE_BLOCK){
			$this->getLevel()->setBlock($block, BlockFactory::get(Block::SUGARCANE_BLOCK), true);

			return true;
		}elseif($down->getId() === self::GRASS or $down->getId() === self::DIRT or $down->getId() === self::SAND){
			$block0 = $down->getSide(Vector3::SIDE_NORTH);
			$block1 = $down->getSide(Vector3::SIDE_SOUTH);
			$block2 = $down->getSide(Vector3::SIDE_WEST);
			$block3 = $down->getSide(Vector3::SIDE_EAST);
			if(($block0 instanceof Water) or ($block1 instanceof Water) or ($block2 instanceof Water) or ($block3 instanceof Water)){
				$this->getLevel()->setBlock($block, BlockFactory::get(Block::SUGARCANE_BLOCK), true);

				return true;
			}
		}

		return false;
	}
}