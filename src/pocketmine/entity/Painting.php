<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\network\mcpe\protocol\AddPaintingPacket;
use pocketmine\Player;

class Painting extends Hanging{
	const NETWORK_ID = 83;

	private $motive;

	public function initEntity(){
		$this->setMaxHealth(1);
		parent::initEntity();

		if(isset($this->namedtag->Motive)){
			$this->motive = $this->namedtag["Motive"];
		}else $this->close();
	}
	public function attack(EntityDamageEvent $source){
		parent::attack($source);
		if($source->isCancelled()) return false;
		$this->level->addParticle(new DestroyBlockParticle($this->add(0.5), BlockFactory::get(Block::LADDER)));
		$this->close();
		return true;
	}
	public function spawnTo(Player $player){
		$pk = new AddPaintingPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->direction = $this->getDirection();
		$pk->title = $this->motive;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	protected function updateMovement(bool $teleport = false){
	}
	public function getDrops(){
		return [ItemItem::get(ItemItem::PAINTING, 0, 1)];
	}
}