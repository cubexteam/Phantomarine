<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Liquid;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityEatItemEvent;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;

class ChorusFruit extends Food{

	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::CHORUS_FRUIT, $meta, $count, "Chorus Fruit");
	}

	public function getCooldownTicks() : int{
		return 20;
	}
	public function canBeConsumedBy(Entity $entity) : bool{
		return $entity instanceof Human and $this->canBeConsumed();
	}

	public function getFoodRestore() : int{
		return 4;
	}

	public function getSaturationRestore() : float{
		return 2.4;
	}

	public function requiresHunger() : bool{
		return false;
	}

	public function onConsume(Entity $consumer){
		$pk = new EntityEventPacket();
		$pk->entityRuntimeId = $consumer->getId();
		$pk->event = EntityEventPacket::USE_ITEM;
		if($consumer instanceof Player){
			$consumer->dataPacket($pk);
		}
		$consumer->getLevel()->getServer()->broadcastPacket($consumer->getViewers(), $pk);

		$consumer->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityEatItemEvent($consumer, $this));

		$level = $consumer->getLevel();
		assert($level !== null);

		$minX = $consumer->getFloorX() - 8;
		$minY = min($consumer->getFloorY(), $consumer->getLevel()->getWorldHeight()) - 8;
		$minZ = $consumer->getFloorZ() - 8;

		$maxX = $minX + 16;
		$maxY = $minY + 16;
		$maxZ = $minZ + 16;

		for($attempts = 0; $attempts < 16; ++$attempts){
			$x = mt_rand($minX, $maxX);
			$y = mt_rand($minY, $maxY);
			$z = mt_rand($minZ, $maxZ);

			while($y >= 0 and !$level->getBlockAt($x, $y, $z)->isSolid()){
				$y--;
			}
			if($y < 0){
				continue;
			}

			$blockUp = $level->getBlockAt($x, $y + 1, $z);
			$blockUp2 = $level->getBlockAt($x, $y + 2, $z);
			if($blockUp->isSolid() or $blockUp instanceof Liquid or $blockUp2->isSolid() or $blockUp2 instanceof Liquid){
				continue;
			}

			$level->addSound(new EndermanTeleportSound($consumer->asVector3()));
			$consumer->teleport(new Vector3($x + 0.5, $y + 1, $z + 0.5));
			$level->addSound(new EndermanTeleportSound($consumer->asVector3()));

			$consumer->getInventory()->setItemInHand($ev->getResidue());
		}
	}
}