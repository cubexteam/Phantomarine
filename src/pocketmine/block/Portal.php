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

class Portal extends Transparent{

	protected $id = self::PORTAL;
	private $temporalVector = null;
	public function __construct($meta = 0){
		$this->meta = $meta;
		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}
	}
	public function getName() : string{
		return "Portal";
	}
	public function getHardness(){
		return -1;
	}
	public function getResistance(){
		return 0;
	}
	public function getToolType(){
		return Tool::TYPE_PICKAXE;
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}

	public function getCollisionBoxes() : array{
		return [];
	}
	public function hasEntityCollision(){
		return true;
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		$block = $this;
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
		return parent::onBreak($item, $player);
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($player instanceof Player){
			$this->meta = $player->getDirection() & 0x01;
		}
		$this->getLevel()->setBlock($block, $this, true, true);

		return true;
	}
	public function getDrops(Item $item) : array{
		return [];
	}
}