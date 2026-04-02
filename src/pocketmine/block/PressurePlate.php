<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\sound\GenericSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

class PressurePlate extends RedstoneSource{
	protected $activateTime = 0;
	protected $canActivate = true;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function hasEntityCollision(){
		return true;
	}
	public function onEntityInside(Entity $entity) : bool{
		if($this->getLevel()->getServer()->redstoneEnabled and $this->canActivate){
			if(!$this->isActivated()){
				$this->meta = 1;
				$this->getLevel()->setBlock($this, $this, true, false);
				$this->getLevel()->addSound(new GenericSound($this, 1000));
			}
			if(!$this->isActivated() or ($this->isActivated() and ($this->getLevel()->getServer()->getTick() % 30) == 0)){
				$this->activate();
			}
		}
		return true;
	}
	public function isActivated(Block $from = null){
		return ($this->meta == 0) ? false : true;
	}

	public function onNearbyBlockChange() : void{
		$below = $this->getSide(Vector3::SIDE_DOWN);
		if ($below instanceof Transparent) {
			$this->getLevel()->useBreakOn($this);
		}
	}

	/*
	public function onScheduledUpdate() : void{
		if($this->isActivated()){
			if(!$this->isCollided()){
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true, false);
				$this->deactivate();
			}
		}
	}
	*/

	public function checkActivation(){
		if($this->isActivated()){
			if((($this->getLevel()->getServer()->getTick() - $this->activateTime)) >= 3){
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this, true, false);
				$this->deactivate();
			}
		}
	}

	/*public function isCollided(){
		foreach($this->getLevel()->getEntities() as $p){
			$blocks = $p->getBlocksAround();
			if(isset($blocks[Level::blockHash($this->x, $this->y, $this->z)])) return true;
		}
		return false;
	}*/
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$below = $this->getSide(Vector3::SIDE_DOWN);
		if($below instanceof Transparent) return;
		else $this->getLevel()->setBlock($block, $this, true, false);
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		if($this->isActivated()){
			$this->meta = 0;
			$this->deactivate();
		}
		$this->canActivate = false;
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);
		return true;
	}
	public function getHardness(){
		return 0.5;
	}
	public function getResistance(){
		return 2.5;
	}
}
