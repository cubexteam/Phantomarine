<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\sound\ItemFrameAddItemSound;
use pocketmine\level\sound\ItemFrameRotateItemSound;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\{ByteTag, CompoundTag, FloatTag, IntTag, StringTag};
use pocketmine\Player;
use pocketmine\tile\ItemFrame as TileItemFrame;
use pocketmine\tile\Tile;

class ItemFrame extends Flowable{
	protected $id = Block::ITEM_FRAME_BLOCK;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "Item Frame";
	}
	public function onActivate(Item $item, Player $player = null){
		if(!(($tile = $this->level->getTile($this)) instanceof TileItemFrame)){
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::ITEM_FRAME),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z),
				new FloatTag("ItemDropChance", 1.0),
				new ByteTag("ItemRotation", 0)
			]);
			$tile = Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), $nbt);
		}

		if($tile->hasItem()){
			$tile->setItemRotation(($tile->getItemRotation() + 1) % 8);
			$this->getLevel()->addSound(new ItemFrameRotateItemSound($this));
		}elseif(!$item->isNull()){
			$tile->setItem($item->pop());
			$this->getLevel()->addSound(new ItemFrameAddItemSound($this));
		}

		return true;
	}
	public function onBreak(Item $item, Player $player = null) : bool{
		if(($tile = $this->level->getTile($this)) instanceof TileItemFrame){
			if(lcg_value() <= $tile->getItemDropChance() and $tile->getItem()->getId() !== Item::AIR){
				$this->level->dropItem($tile->getBlock(), $tile->getItem());
			}
		}
		return parent::onBreak($item, $player);
	}

	public function onNearbyBlockChange() : void{
		$sides = [
			0 => Vector3::SIDE_WEST,
			1 => Vector3::SIDE_EAST,
			2 => Vector3::SIDE_NORTH,
			3 => Vector3::SIDE_SOUTH
		];
		if(!$this->getSide($sides[$this->meta])->isSolid()){
			$this->level->useBreakOn($this);
		}
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face === 0 or $face === 1 or !$target->isSolid()){
			return false;
		}

		$faces = [
			2 => 3,
			3 => 2,
			4 => 1,
			5 => 0
		];

		$this->meta = $faces[$face];
		$this->level->setBlock($block, $this, true, true);

		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::ITEM_FRAME),
			new IntTag("x", $block->x),
			new IntTag("y", $block->y),
			new IntTag("z", $block->z),
			new FloatTag("ItemDropChance", 1.0),
			new ByteTag("ItemRotation", 0)
		]);

		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}

		Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), $nbt);

		return true;

	}
	public function getDrops(Item $item) : array{
		return [
			[Item::ITEM_FRAME, 0, 1]
		];
	}

}