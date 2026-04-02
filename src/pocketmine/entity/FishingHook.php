<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\block\Water;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\fish\FishingRodCaughtEntityEvent;
use pocketmine\event\player\fish\FishingRodCaughtFishEvent;
use pocketmine\event\player\fish\FishingRodHookedEntityEvent;
use pocketmine\item\FishingRod;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\Player;
use pocketmine\utils\Random;
use function abs;
use function cos;
use function floor;
use function lcg_value;
use function mt_rand;
use function sin;
use const M_PI;

class FishingHook extends Projectile{
    public const NETWORK_ID = 77;
    public $width = 0.2;
    public $height = 0.2;
    public $gravity = 0.07;
    public $drag = 0.05;
    protected $hookedEntity;
    protected $ticksCatchable = 0;
    protected $ticksCaughtDelay = 0;
    protected $ticksCatchableDelay = 0;
    protected $fishApproachAngle = 0;
    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null){
        $this->random = new Random();
        parent::__construct($level, $nbt, $shootingEntity);
    }
    public function handleHookCasting(float $x, float $y, float $z, float $f1, float $f2){
        $f = sqrt($x * $x + $y * $y + $z * $z);
        $x = $x / $f;
        $y = $y / $f;
        $z = $z / $f;
        $x = $x + $this->random->nextSignedFloat() * 0.0075 * $f2;
        $y = $y + $this->random->nextSignedFloat() * 0.0075 * $f2;
        $z = $z + $this->random->nextSignedFloat() * 0.0075 * $f2;
        $x = $x * $f1;
        $y = $y * $f1;
        $z = $z * $f1;
        $this->motion->x += $x;
        $this->motion->y += $y;
        $this->motion->z += $z;
    }

    public function attack(EntityDamageEvent $source): void
    {
        if(!$source instanceof EntityDamageByEntityEvent){
            parent::attack($source);
        }
    }
    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
        $entityHit->attack(new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, 0));

        if($this->getOwningEntity() instanceof Player){
            $ev = new FishingRodHookedEntityEvent($this->getOwningEntity(), $this, $entityHit);
            $this->server->getPluginManager()->callEvent($ev);
            if(!$ev->isCancelled()){
                $this->setHookedEntity($entityHit);
            }
        }
    }

    public function setHookedEntity(Entity $entity) : void{
        $this->setDataProperty(self::DATA_RIDER_SEAT_POSITION, self::DATA_TYPE_VECTOR3F, [0, $entity->height * 0.15, 0]);

        $pk = new SetEntityLinkPacket();
        $pk->from = $entity->getId();
        $pk->to = $this->getId();
        $pk->type = SetEntityLinkPacket::TYPE_PASSENGER;
        $this->server->broadcastPacket($this->getViewers(), $pk);

        $this->hookedEntity = $entity;
    }
    public function getHookedEntity() : ?Entity{
        return $this->hookedEntity;
    }

    public function releaseHookedEntity() : void{
        if($this->hookedEntity !== null){
            $pk = new SetEntityLinkPacket();
            $pk->from = $this->hookedEntity->getId();
            $pk->to = $this->getId();
            $pk->type = SetEntityLinkPacket::TYPE_REMOVE;
            $this->server->broadcastPacket($this->getViewers(), $pk);
        }
        $this->hookedEntity = null;
    }

    public function entityBaseTick($tickDiff = 1) : bool{
        if($this->closed) return false;

        $owner = $this->getOwningEntity();

        $inGround = $this->level->getBlock($this)->isSolid();

        if($inGround){
            $this->motion->x *= $this->random->nextFloat() * 0.2;
            $this->motion->y *= $this->random->nextFloat() * 0.2;
            $this->motion->z *= $this->random->nextFloat() * 0.2;
        }

        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($owner instanceof Player){
            if($owner->isClosed() or !$owner->isAlive() or !($owner->getInventory()->getItemInHand() instanceof FishingRod) or $owner->distanceSquared($this) > 1024){
                $this->flagForDespawn();
            }

            if(!$inGround){
                $hasUpdate = true;

                $f6 = 0.92;

                if($this->onGround or $this->isCollidedHorizontally){
                    $f6 = 0.5;
                }

                $d10 = 0;

                $bb = $this->getBoundingBox();

                for($j = 0; $j < 5; ++$j){
                    $d1 = $bb->minY + ($bb->maxY - $bb->minY) * $j / 5;
                    $d3 = $bb->minY + ($bb->maxY - $bb->minY) * ($j + 1) / 5;

                    $bb2 = new AxisAlignedBB($bb->minX, $d1, $bb->minZ, $bb->maxX, $d3, $bb->maxZ);

                    if($this->level->isLiquidInBoundingBox($bb2, new Water())){
                        $d10 += 0.2;
                    }
                }

                if($this->isValid() and $d10 > 0){
                    $l = 1;


                    if($this->ticksCatchable > 0){
                        --$this->ticksCatchable;

                        if($this->ticksCatchable <= 0){
                            $this->ticksCaughtDelay = 0;
                            $this->ticksCatchableDelay = 0;
                        }
                    }elseif($this->ticksCatchableDelay > 0){
                        $this->ticksCatchableDelay -= $l;

                        if($this->ticksCatchableDelay <= 0){
                            $this->broadcastEntityEvent(EntityEventPacket::FISH_HOOK_HOOK);
                            $this->motion->y -= 0.2;
                            $this->ticksCatchable = mt_rand(10, 30);
                        }else{
                            $this->fishApproachAngle = $this->fishApproachAngle + $this->random->nextSignedFloat() * 4.0;
                            $f7 = $this->fishApproachAngle * 0.01745;
                            $f10 = sin($f7);
                            $f11 = cos($f7);
                            $d13 = $this->x + ($f10 * $this->ticksCatchableDelay * 0.1);
                            $d15 = $this->y + 1;
                            $d16 = $this->z + ($f11 * $this->ticksCatchableDelay * 0.1);
                            $block1 = $this->level->getBlock(new Vector3($d13, $d15 - 1, $d16));

                            if($block1 instanceof Water){
                                if($this->random->nextFloat() < 0.15){
                                    $this->level->addParticle(new GenericParticle(new Vector3($d13, $d15 - 0.1, $d16), Particle::TYPE_BUBBLE));
                                }

                                $this->level->addParticle(new GenericParticle(new Vector3($d13, $d15, $d16), Particle::TYPE_WATER_WAKE));
                            }
                        }
                    }elseif($this->ticksCaughtDelay > 0){
                        $this->ticksCaughtDelay -= $l;
                        $f1 = 0.15;

                        if($this->ticksCaughtDelay < 20){
                            $f1 = ($f1 + (20 - $this->ticksCaughtDelay) * 0.05);
                        }elseif($this->ticksCaughtDelay < 40){
                            $f1 = ($f1 + (40 - $this->ticksCaughtDelay) * 0.02);
                        }elseif($this->ticksCaughtDelay < 60){
                            $f1 = ($f1 + (60 - $this->ticksCaughtDelay) * 0.01);
                        }

                        if($this->random->nextFloat() < $f1){
                            $f9 = mt_rand(0, 360) * 0.01745;
                            $f2 = mt_rand(25, 60);
                            $d12 = $this->x + (sin($f9) * $f2 * 0.1);
                            $d14 = floor($this->y) + 1.0;
                            $d6 = $this->z + (cos($f9) * $f2 * 0.1);
                            $block = $this->level->getBlock(new Vector3($d12, $d14 - 1, $d6));

                            if($block instanceof Water){
                                $this->level->addParticle(new GenericParticle(new Vector3($d12, $d14, $d6), Particle::TYPE_SPLASH));
                            }
                        }

                        if($this->ticksCaughtDelay <= 0){
                            $this->ticksCatchableDelay = mt_rand(20, 80);
                            $this->fishApproachAngle = mt_rand(0, 360);
                        }
                    }else{
                        $this->ticksCaughtDelay = mt_rand(100, 900);
                        $this->ticksCaughtDelay -= 20 * 5;
                    }

                    if($this->ticksCatchable > 0){
                        $this->motion->y -= ($this->random->nextFloat() * $this->random->nextFloat() * $this->random->nextFloat()) * 0.2;
                    }
                }

                $d11 = $d10 * 2.0 - 1.0;
                $this->motion->y += 0.04 * $d11;

                if($d10 > 0.0){
                    $f6 = $f6 * 0.9;
                    $this->motion->y *= 0.8;
                }

                $this->motion->x *= $f6;
                $this->motion->y *= $f6;
                $this->motion->z *= $f6;
            }
        }else{
            $this->flagForDespawn();
        }

        return $hasUpdate;
    }

    public function canBeMovedByCurrents() : bool{
        return false;
    }

    protected function tryChangeMovement() : void{
    }

    public function close() : void{
        $owner = $this->getOwningEntity();
        if($owner instanceof Player and $owner->getFishingHook() === $this){
            $owner->setFishingHook(null);
        }

        $this->releaseHookedEntity();
        parent::close();
    }

    public function handleHookRetraction() : int{
        $damage = 0;
        if($this->getOwningEntity() instanceof Player){
            if($this->hookedEntity !== null){
                $ev = new FishingRodCaughtEntityEvent($this->getOwningEntity(), $this, $this->hookedEntity, 0.1);
                $this->server->getPluginManager()->callEvent($ev);

                if(!$ev->isCancelled()){
                    $eyePos = $this->getOwningEntity()->add(0, $this->getOwningEntity()->getEyeHeight(), 0);
                    $this->hookedEntity->setMotion($eyePos->subtract($this)->multiply($ev->getForce()));
                    $damage = 3;
                }
            }elseif($this->ticksCatchable > 0){
                $rndCatch = mt_rand(0, 100);
                if($rndCatch < 81){
                    $items = [
                        ItemIds::RAW_FISH, ItemIds::PUFFER_FISH, ItemIds::RAW_SALMON, ItemIds::CLOWN_FISH
                    ];
                }elseif($rndCatch < 101){
                    $items = [
                        ItemIds::CLOCK, ItemIds::COMPASS, ItemIds::FLINT_STEEL, ItemIds::GLASS_BOTTLE, ItemIds::LEATHER_BOOTS, ItemIds::ROTTEN_FLESH, ItemIds::STICK, ItemIds::FISHING_ROD, ItemIds::RABBIT_FOOT, ItemIds::ENCHANTING_BOTTLE, ItemIds::DEAD_BUSH, ItemIds::BUCKET, ItemIds::BONE, ItemIds::FLOWER_POT
                    ];
                }
                $randomFish = $items[mt_rand(0, count($items) - 1)];
                $result = ItemItem::get($randomFish);

                $ev = new FishingRodCaughtFishEvent($this->getOwningEntity(), $this);
                $this->server->getPluginManager()->callEvent($ev);

                $angler = $this->getOwningEntity();

                if(!$ev->isCancelled()){
                    $nbt = Entity::createBaseNBT($this);
                    $nbt->Item = $result->nbtSerialize(-1, "Item");

                    $entityitem = new Item($this->level, $nbt);
                    $d0 = $angler->x - $this->x;
                    $d2 = $angler->y - $this->y;
                    $d4 = $angler->z - $this->z;
                    $d6 = sqrt($d0 * $d0 + $d2 * $d2 + $d4 * $d4);
                    $d8 = 0.1;
                    $entityitem->setMotion(new Vector3($d0 * $d8, $d2 * $d8 + sqrt($d6) * 0.08, $d4 * $d8));
                    $entityitem->spawnToAll();
                    $angler->addXp(mt_rand(1, 6));
                    $damage = 1;
                }
            }

            $this->flagForDespawn();
        }

        return $damage;
    }
    public function spawnTo(Player $player){
        $pk = new AddEntityPacket();
        $pk->type = FishingHook::NETWORK_ID;
        $pk->entityRuntimeId = $this->getId();
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->speedX = $this->motion->x;
        $pk->speedY = $this->motion->y;
        $pk->speedZ = $this->motion->z;
        $pk->metadata = $this->dataProperties;
        $player->dataPacket($pk);
        parent::spawnTo($player);
    }
}