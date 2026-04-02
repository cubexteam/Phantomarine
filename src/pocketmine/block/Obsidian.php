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

class Obsidian extends Solid{

	protected $id = self::OBSIDIAN;
	private $temporalVector = null;
	public function __construct($meta = 0){
		$this->meta = $meta;
		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}
	}
	public function getName() : string{
		return "Obsidian";
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}
	public function getHardness(){
		return 35;
	}
	public function getBlastResistance() : float{
		return 6000;
	}
	public function getDrops(Item $item) : array{
		if($item->isPickaxe() >= 5){
			return [
				[Item::OBSIDIAN, 0, 1],
			];
		}else{
			return [];
		}
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		parent::onBreak($item, $player);

		if($this->getLevel()->getServer()->netherEnabled){
			for($i = 0; $i <= 6; $i++){
				if($this->getSide($i)->getId() == self::PORTAL){
					break;
				}
				if($i == 6){
					return true;
				}
			}
			$block = $this->getSide($i);
			if($this->getLevel()->getBlockAt($block->x - 1, $block->y, $block->z)->getId() == Block::PORTAL or
				$this->getLevel()->getBlockAt($block->x + 1, $block->y, $block->z)->getId() == Block::PORTAL
			){
				for($x = $block->x; $this->getLevel()->getBlockAt($x, $block->y, $block->z)->getId() == Block::PORTAL; $x++){
					for($y = $block->y; $this->getLevel()->getBlockAt($x, $y, $block->z)->getId() == Block::PORTAL; $y++){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), BlockFactory::get(Block::AIR));
					}
					for($y = $block->y - 1; $this->getLevel()->getBlockAt($x, $y, $block->z)->getId() == Block::PORTAL; $y--){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), BlockFactory::get(Block::AIR));
					}
				}
				for($x = $block->x - 1; $this->getLevel()->getBlockAt($x, $block->y, $block->z)->getId() == Block::PORTAL; $x--){
					for($y = $block->y; $this->getLevel()->getBlockAt($x, $y, $block->z)->getId() == Block::PORTAL; $y++){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), BlockFactory::get(Block::AIR));
					}
					for($y = $block->y - 1; $this->getLevel()->getBlockAt($x, $y, $block->z)->getId() == Block::PORTAL; $y--){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), BlockFactory::get(Block::AIR));
					}
				}
			}else{
				for($z = $block->z; $this->getLevel()->getBlockAt($block->x, $block->y, $z)->getId() == Block::PORTAL; $z++){
					for($y = $block->y; $this->getLevel()->getBlockAt($block->x, $y, $z)->getId() == Block::PORTAL; $y++){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), BlockFactory::get(Block::AIR));
					}
					for($y = $block->y - 1; $this->getLevel()->getBlockAt($block->x, $y, $z)->getId() == Block::PORTAL; $y--){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), BlockFactory::get(Block::AIR));
					}
				}
				for($z = $block->z - 1; $this->getLevel()->getBlockAt($block->x, $block->y, $z)->getId() == Block::PORTAL; $z--){
					for($y = $block->y; $this->getLevel()->getBlockAt($block->x, $y, $z)->getId() == Block::PORTAL; $y++){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), BlockFactory::get(Block::AIR));
					}
					for($y = $block->y - 1; $this->getLevel()->getBlockAt($block->x, $y, $z)->getId() == Block::PORTAL; $y--){
						$this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), BlockFactory::get(Block::AIR));
					}
				}
			}
		}

		return true;
	}
}