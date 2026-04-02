<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\entity\Arrow;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\level\sound\TNTPrimeSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;
use pocketmine\utils\Random;

class TNT extends Solid implements ElectricalAppliance{

	protected $id = self::TNT;
	public function __construct($meta = 0){
		$this->meta = $meta;
	}
	public function getName() : string{
		return "TNT";
	}
	public function getHardness(){
		return 0;
	}

	public function hasEntityCollision(){
		return true;
	}
	public function getBurnChance() : int{
		return 15;
	}
	public function getBurnAbility() : int{
		return 100;
	}

	public function onIncinerate() : void{
		$this->prime();
	}
	public function prime(Player $player = null){
		$this->meta = 1;
		if($player != null and $player->isCreative()){
			$dropItem = false;
		}else{
			$dropItem = true;
		}
		$mot = (new Random())->nextSignedFloat() * M_PI * 2;
		$tnt = Entity::createEntity("PrimedTNT", $this->getLevel(), new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $this->x + 0.5),
				new DoubleTag("", $this->y),
				new DoubleTag("", $this->z + 0.5)
			]),
			new ListTag("Motion", [
				new DoubleTag("", -sin($mot) * 0.02),
				new DoubleTag("", 0.2),
				new DoubleTag("", -cos($mot) * 0.02)
			]),
			new ListTag("Rotation", [
				new FloatTag("", 0),
				new FloatTag("", 0)
			]),
			new ShortTag("Fuse", 80)
		]), $dropItem, $player);

		$tnt->spawnToAll();
		$this->level->addSound(new TNTPrimeSound($this));
	}

	public function onEntityInside(Entity $entity) : bool{
		if($entity instanceof Arrow and $entity->isOnFire()){
			$this->prime();
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);
			return false;
		}
		return true;
	}

	public function onScheduledUpdate() : void{
		$sides = [0, 1, 2, 3, 4, 5];
		foreach($sides as $side){
			$block = $this->getSide($side);
			if($block instanceof RedstoneSource and $block->isActivated($this)){
				$this->prime();
				$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);
				break;
			}
		}
	}
	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$this->getLevel()->setBlock($this, $this, true, false);

		$this->getLevel()->scheduleUpdate($this, 40);
	}
	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::FLINT_STEEL or $item->hasEnchantment(Enchantment::TYPE_WEAPON_FIRE_ASPECT)){
			$this->prime($player);
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);

			$item->useOn($this);

			return true;
		}

		return false;
	}
}