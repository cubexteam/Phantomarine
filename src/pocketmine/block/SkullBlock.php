<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Skull as SkullTile;
use pocketmine\tile\Tile;

class SkullBlock extends Flowable{

	protected $id = self::SKULL_BLOCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getHardness(){
		return 1;
	}
	public function getName() : bool{
		return "Mob Head";
	}
	protected function recalculateBoundingBox(){
		return new AxisAlignedBB(
			$this->x + 0.25,
			$this->y,
			$this->z + 0.25,
			$this->x + 0.75,
			$this->y + 0.5,
			$this->z + 0.75
		);
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face !== 0){
			$this->meta = $face;
			$rot = 0;
			if($face === Vector3::SIDE_UP and $player !== null){
				$rot = floor(($player->yaw * 16 / 360) + 0.5) & 0x0F;
			}
			$this->getLevel()->setBlock($block, $this, true);
			$moveMouth = false;
			if($item->getDamage() === SkullTile::TYPE_DRAGON){
				if(in_array($target->getId(), [Block::REDSTONE_TORCH, Block::REDSTONE_BLOCK])) $moveMouth = true;
			}
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::SKULL),
				new ByteTag("SkullType", $item->getDamage()),
				new ByteTag("Rot", $rot),
				new ByteTag("MouthMoving", (bool) $moveMouth),
				new IntTag("x", (int) $this->x),
				new IntTag("y", (int) $this->y),
				new IntTag("z", (int) $this->z)
			]);
			if($item->hasCustomName()){
				$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
			}
			Tile::createTile("Skull", $this->getLevel(), $nbt);

			if($item->getDamage() === 1 and $player instanceof Player){
				$level = $player->getLevel();
				if($level->getBlock($target->asVector3())->getId() === 88){
					if($level->getBlock($target->asVector3()->add(1, 0, 0)) and $level->getBlock($target->asVector3()->subtract(1, 0, 0))->getId() === 88 and $level->getBlock($target->asVector3()->subtract(0, 1, 0))->getId() === 88 and $level->getTile($target->asVector3()->add(1, 1, 0)) instanceof SkullTile and $level->getTile($target->asVector3()->add(0, 1, 0)->subtract(1, 0, 0)) instanceof SkullTile){
						$level->setBlock($target->asVector3(), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->subtract(0, 1, 0), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->subtract(1, 0, 0), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(1, 0, 0), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(0, 1, 0), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(1, 1, 0), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(0, 1, 0)->subtract(1, 0, 0), BlockFactory::get(0, 0));
						return true;
					}
					if($level->getBlock($target->asVector3()->add(0, 0, 1)) and $level->getBlock($target->asVector3()->subtract(0, 0, 1))->getId() === 88 and $level->getBlock($target->asVector3()->subtract(0, 1, 0))->getId() === 88 and $level->getTile($target->asVector3()->add(0, 1, 1)) instanceof SkullTile and $level->getTile($target->asVector3()->add(0, 1, 0)->subtract(0, 0, 1)) instanceof SkullTile){
						$level->setBlock($target->asVector3(), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->subtract(0, 1, 0), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->subtract(0, 0, 1), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(0, 0, 1), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(0, 1, 0), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(0, 1, 1), BlockFactory::get(0, 0));
						$level->setBlock($target->asVector3()->add(0, 1, 0)->subtract(0, 0, 1), BlockFactory::get(0, 0));
						return true;
					}
				}
			}

			return true;
		}

		return false;
	}
	public function getDrops(Item $item) : array{
		$tile = $this->level->getTile($this);
		if($tile instanceof SkullTile){
			return [
				[Item::MOB_HEAD, $tile->getType(), 1]
			];
		}
		return [];
	}
}
