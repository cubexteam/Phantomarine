<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Portal;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class FireCharge extends Item{
	private $temporalVector = null;
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::FIRE_CHARGE, $meta, $count, "Fire Charge");
		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}
	}
	public function onActivate(Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$level = $player->getLevel();
		assert($level !== null);
		if($target->getId() === Block::OBSIDIAN and $player->getServer()->netherEnabled){
			$tx = $target->getX();
			$ty = $target->getY();
			$tz = $target->getZ();
			$x_max = $tx;
			$x_min = $tx;
			for($x = $tx + 1; $level->getBlockAt($x, $ty, $tz)->getId() == Block::OBSIDIAN; $x++){
				$x_max++;
			}
			for($x = $tx - 1; $level->getBlockAt($x, $ty, $tz)->getId() == Block::OBSIDIAN; $x--){
				$x_min--;
			}
			$count_x = $x_max - $x_min + 1;
			if($count_x >= 4 and $count_x <= 23){
				$x_max_y = $ty;
				$x_min_y = $ty;
				for($y = $ty; $level->getBlockAt($x_max, $y, $tz)->getId() == Block::OBSIDIAN; $y++){
					$x_max_y++;
				}
				for($y = $ty; $level->getBlockAt($x_min, $y, $tz)->getId() == Block::OBSIDIAN; $y++){
					$x_min_y++;
				}
				$y_max = min($x_max_y, $x_min_y) - 1;
				$count_y = $y_max - $ty + 2;
				if($count_y >= 5 and $count_y <= 23){
					$count_up = 0;
					for($ux = $x_min; ($level->getBlockAt($ux, $y_max, $tz)->getId() == Block::OBSIDIAN and $ux <= $x_max); $ux++){
						$count_up++;
					}
					if($count_up == $count_x){
						for($px = $x_min + 1; $px < $x_max; $px++){
							for($py = $ty + 1; $py < $y_max; $py++){
								$level->setBlock($this->temporalVector->setComponents($px, $py, $tz), new Portal());
							}
						}
						$this->pop();
						return true;
					}
				}
			}

			$z_max = $tz;
			$z_min = $tz;
			for($z = $tz + 1; $level->getBlockAt($tx, $ty, $z)->getId() == Block::OBSIDIAN; $z++){
				$z_max++;
			}
			for($z = $tz - 1; $level->getBlockAt($tx, $ty, $z)->getId() == Block::OBSIDIAN; $z--){
				$z_min--;
			}
			$count_z = $z_max - $z_min + 1;
			if($count_z >= 4 and $count_z <= 23){
				$z_max_y = $ty;
				$z_min_y = $ty;
				for($y = $ty; $level->getBlockAt($tx, $y, $z_max)->getId() == Block::OBSIDIAN; $y++){
					$z_max_y++;
				}
				for($y = $ty; $level->getBlockAt($tx, $y, $z_min)->getId() == Block::OBSIDIAN; $y++){
					$z_min_y++;
				}
				$y_max = min($z_max_y, $z_min_y) - 1;
				$count_y = $y_max - $ty + 2;
				if($count_y >= 5 and $count_y <= 23){
					$count_up = 0;
					for($uz = $z_min; ($level->getBlockAt($tx, $y_max, $uz)->getId() == Block::OBSIDIAN and $uz <= $z_max); $uz++){
						$count_up++;
					}
					if($count_up == $count_z){
						for($pz = $z_min + 1; $pz < $z_max; $pz++){
							for($py = $ty + 1; $py < $y_max; $py++){
								$level->setBlock($this->temporalVector->setComponents($tx, $py, $pz), new Portal());
							}
						}
						$this->pop();
						return true;
					}
				}
			}
		}

		if($block->getId() === self::AIR){
			$level->setBlock($block, BlockFactory::get(Block::FIRE), true);

			$this->pop();

			return true;
		}

		return false;
	}
}