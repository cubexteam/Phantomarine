<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
trait FallableTrait{

	abstract protected function asPosition() : Position;

	abstract protected function getId();

	abstract protected function getDamage();

	public function onNearbyBlockChange() : void{
		$pos = $this->asPosition();
		$down = $pos->level->getBlock($pos->getSide(Facing::DOWN));
		if($down->canBeReplaced()){
			$pos->level->setBlock($pos, BlockFactory::get(Block::AIR));

			$fall = Entity::createEntity("FallingSand", $this->getLevel(), new CompoundTag("", [
				new ListTag("Pos", [
					new DoubleTag("", $this->x + 0.5),
					new DoubleTag("", $this->y),
					new DoubleTag("", $this->z + 0.5)
				]),
				new ListTag("Motion", [
					new DoubleTag("", 0),
					new DoubleTag("", 0),
					new DoubleTag("", 0)
				]),
				new ListTag("Rotation", [
					new FloatTag("", 0),
					new FloatTag("", 0)
				]),
				new IntTag("TileID", $this->getId()),
				new ByteTag("Data", $this->getDamage()),
			]));

			if($fall !== null){
				$fall->spawnToAll();
			}
		}
	}

	public function tickFalling() : ?Block{
		return null;
	}
}