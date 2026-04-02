<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\entity\EnderCrystal as Crystal;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\{CompoundTag, DoubleTag, FloatTag, ListTag};
use pocketmine\Player;

class EnderCrystal extends Item{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::END_CRYSTAL, 0, $count, 'Ender Crystal');
	}
	public function onActivate(Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$id = $target->getId();
		if($id !== BlockIds::OBSIDIAN and $id !== BlockIds::BEDROCK){
			return false;
		}

		$level = $player->getLevel();
		assert($level !== null);

		if(!$level->getBlock($target->asVector3()->add(0, 1, 0))->getId() !== 0){
			$pos = $target;
			$entities = $level->getNearbyEntities(new AxisAlignedBB($pos->getX(), $pos->getY(), $pos->getZ(), $pos->getX() + 1, $pos->getY() + 2, $pos->getZ() + 1));
			if(count($entities) === 0 && $level->getBlock($pos->up()) instanceof Air && $level->getBlock($pos->up(2)) instanceof Air){
				$nbt = new CompoundTag('', [
					new ListTag('Pos', [new DoubleTag('', $target->getX() + 0.5), new DoubleTag('', $target->getY() + 1), new DoubleTag('', $target->getZ() + 0.5)]),
					new ListTag('Motion', [new DoubleTag('', 0.0), new DoubleTag('', 0.0), new DoubleTag('', 0.0)]),
					new ListTag('Rotation', [new FloatTag('', $player->getYaw()), new FloatTag('', $player->getPitch())])
				]);

				$npc = new Crystal($player->level, $nbt);
				$npc->spawnToAll();

				$this->pop();

				return true;
			}
		}

		return false;
	}
}