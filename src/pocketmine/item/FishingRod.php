<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\FishingHook;
use pocketmine\event\player\fish\FishingRodStartFishingEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;

class FishingRod extends Tool{
	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::FISHING_ROD, $meta, $count, "Fishing Rod");
	}

    public function getMaxStackSize() : int{
        return 1;
    }

	public function getMaxDurability(){
		return 65;
	}

    public function onClickAir(Player $player, Vector3 $directionVector) : bool{
        $hook = $player->getFishingHook();
        if($hook === null or $hook->isFlaggedForDespawn()){
            $hook = new FishingHook($player->level, Entity::createBaseNBT($player->add(0, $player->getEyeHeight(), 0)), $player);

            $ev = new FishingRodStartFishingEvent($player, $hook, 1.0, 0.75, 1.0);
            Server::getInstance()->getPluginManager()->callEvent($ev);

            if(!$ev->isCancelled()){
                $hook->setMotion($directionVector->normalize()->multiply($ev->getForce()));

                $player->setFishingHook($hook);

                $motion = $hook->getMotion();
                $hook->handleHookCasting($motion->x, $motion->y, $motion->z, $ev->getF1(), $ev->getF2());

                $player->level->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_THROW, 0, 0x13f);
                $hook->spawnToAll();
            }else{
                $hook->flagForDespawn();
            }
        }else{
            $damage = $hook->handleHookRetraction();

            if($player->isSurvival() and $damage !== 0){
                $this->applyDamage($damage);
                $player->getInventory()->setItemInHand($this);
            }
        }

        return true;
    }
} 
