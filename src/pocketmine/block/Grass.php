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
use pocketmine\level\generator\object\TallGrass as TallGrassObject;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\Random;

class Grass extends Solid{

	protected $id = self::GRASS;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Grass";
	}
	public function getHardness(){
		return 0.6;
	}
	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}
	public function getDrops(Item $item) : array{
		if($item->getEnchantmentLevel(Enchantment::TYPE_MINING_SILK_TOUCH) > 0){
			return [
				[Item::GRASS, 0, 1],
			];
		}else{
			return [
				[Item::DIRT, 0, 1],
			];
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$lightAbove = $this->level->getFullLightAt($this->x, $this->y + 1, $this->z);
		if($lightAbove < 4 and BlockFactory::$lightFilter[$this->level->getBlockIdAt($this->x, $this->y + 1, $this->z)] >= 3){
			$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($this, $this, BlockFactory::get(BlockIds::DIRT)));
			if(!$ev->isCancelled()){
				$this->level->setBlock($this, $ev->getNewState(), false, false);
			}
		}elseif($lightAbove >= 9){
			for($i = 0; $i < 4; ++$i){
				$x = mt_rand($this->x - 1, $this->x + 1);
				$y = mt_rand($this->y - 3, $this->y + 1);
				$z = mt_rand($this->z - 1, $this->z + 1);
				if(
					$this->level->getBlockIdAt($x, $y, $z) !== BlockIds::DIRT or
					$this->level->getBlockDataAt($x, $y, $z) === 1 or
					$this->level->getFullLightAt($x, $y + 1, $z) < 4 or
					BlockFactory::$lightFilter[$this->level->getBlockIdAt($x, $y + 1, $z)] >= 3
				){
					continue;
				}

				$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockSpreadEvent($b = $this->level->getBlockAt($x, $y, $z), $this, BlockFactory::get(Block::GRASS)));
				if(!$ev->isCancelled()){
					$this->level->setBlock($b, $ev->getNewState(), false, false);
				}
			}
		}
	}
	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::DYE and $item->getDamage() === 0x0F){
			$item->pop();
			TallGrassObject::growGrass($this->getLevel(), $this, new Random(mt_rand()), 8, 2);

			return true;
		}elseif($item->isHoe()){
			$item->useOn($this);
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::FARMLAND));

			return true;
		}elseif($item->isShovel() and $this->getSide(Vector3::SIDE_UP)->getId() === Block::AIR){
			$item->useOn($this);
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::GRASS_PATH));

			return true;
		}

		return false;
	}
}
