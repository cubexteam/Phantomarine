<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityEatBlockEvent;
use pocketmine\item\FoodSource;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Cake extends Transparent implements FoodSource{

	protected $id = self::CAKE_BLOCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 0.5;
	}
	public function getName() : string{
		return "Cake Block";
	}
	protected function recalculateBoundingBox(){

		$f = $this->getDamage() * 0.125;

		return new AxisAlignedBB(
			$this->x + 0.0625 + $f,
			$this->y,
			$this->z + 0.0625,
			$this->x + 1 - 0.0625,
			$this->y + 0.5,
			$this->z + 1 - 0.0625
		);
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() !== self::AIR){
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
			$this->getLevel()->setBlock($this, BlockFactory::get(BlockIds::AIR), true);
		}
	}
	public function getDrops(Item $item) : array{
		return [];
	}
	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player and $player->getFood() < $player->getMaxFood()){
			$player->getServer()->getPluginManager()->callEvent($ev = new EntityEatBlockEvent($player, $this));

			if(!$ev->isCancelled()){
				$player->addFood($ev->getFoodRestore());
				$player->addSaturation($ev->getSaturationRestore());
				foreach($ev->getAdditionalEffects() as $effect){
					$player->addEffect($effect);
				}

				$this->getLevel()->setBlock($this, $ev->getResidue());
				return true;
			}
		}

		return false;
	}
	public function getFoodRestore() : int{
		return 2;
	}
	public function getSaturationRestore() : float{
		return 0.4;
	}
	public function getResidue(){
		$clone = clone $this;
		$clone->meta++;
		if($clone->meta > 0x06){
			$clone = BlockFactory::get(Block::AIR);
		}
		return $clone;
	}
	public function getAdditionalEffects() : array{
		return [];
	}
}
