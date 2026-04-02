<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use InvalidArgumentException;
use InvalidStateException;
use pocketmine\event\entity\EntityConsumeTotemEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\inventory\EnderChestInventory;
use pocketmine\inventory\FloatingInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\OffhandInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\SimpleTransactionQueue;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemIds;
use pocketmine\item\Totem;
use pocketmine\level\Level;
use pocketmine\math\Math;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;
use ReflectionClass;
use function array_merge;
use function max;
use function min;
use function random_int;
use function strlen;

class Human extends Creature implements ProjectileSource, InventoryHolder{

    const INT32_MIN = -0x7fffffff - 1;
    const INT32_MAX = 0x7fffffff;

    const DATA_PLAYER_FLAG_SLEEP = 1;
    const DATA_PLAYER_FLAG_DEAD = 2;

    const DATA_PLAYER_FLAGS = 27;

    const DATA_PLAYER_BED_POSITION = 29;
    protected $inventory;
    protected $enderChestInventory;
    protected $floatingInventory;
    protected $offhandInventory;
    protected $transactionQueue = null;
    protected $uuid;
    protected $rawUUID;

    public $width = 0.6;
    public $height = 1.8;
    public $eyeHeight = 1.62;

    protected $skinId;
    protected $skin;
    protected $foodTickTimer = 0;
    protected $totalXp = 0;
    protected $xpSeed;
    protected $xpCooldown = 0;

    protected $baseOffset = 1.62;
    public function getSkinData(){
        return $this->skin;
    }
    public function getSkinId(){
        return $this->skinId;
    }
    public function getUniqueId(){
        return $this->uuid;
    }
    public function getRawUniqueId(){
        return $this->rawUUID;
    }
    public function setSkin($str, $skinId){
        $this->skin = $str;
        $this->skinId = $skinId;
    }

    public function jump() : void{
        parent::jump();
        if($this->isSprinting()){
            $this->exhaust(0.8, PlayerExhaustEvent::CAUSE_SPRINT_JUMPING);
        }else{
            $this->exhaust(0.2, PlayerExhaustEvent::CAUSE_JUMPING);
        }
    }
    public function getFood() : float{
        return $this->attributeMap->getAttribute(Attribute::HUNGER)->getValue();
    }
    public function setFood(float $new){
        $attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
        $old = $attr->getValue();
        $attr->setValue($new);

        $reset = false;
        foreach([17, 6, 0] as $bound){
            if(($old > $bound) !== ($new > $bound)){
                $reset = true;
                break;
            }
        }
        if($reset){
            $this->foodTickTimer = 0;
        }

    }
    public function getMaxFood() : float{
        return $this->attributeMap->getAttribute(Attribute::HUNGER)->getMaxValue();
    }
    public function addFood(float $amount){
        $attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
        $amount += $attr->getValue();
        $amount = max(min($amount, $attr->getMaxValue()), $attr->getMinValue());
        $this->setFood($amount);
    }
    public function isHungry() : bool{
        return $this->getFood() < $this->getMaxFood();
    }
    public function getSaturation() : float{
        return $this->attributeMap->getAttribute(Attribute::SATURATION)->getValue();
    }
    public function setSaturation(float $saturation){
        $this->attributeMap->getAttribute(Attribute::SATURATION)->setValue($saturation);
    }
    public function addSaturation(float $amount){
        $attr = $this->attributeMap->getAttribute(Attribute::SATURATION);
        $attr->setValue($attr->getValue() + $amount, true);
    }
    public function getExhaustion() : float{
        return $this->attributeMap->getAttribute(Attribute::EXHAUSTION)->getValue();
    }
    public function setExhaustion(float $exhaustion){
        $this->attributeMap->getAttribute(Attribute::EXHAUSTION)->setValue($exhaustion);
    }
    public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
        $this->server->getPluginManager()->callEvent($ev = new PlayerExhaustEvent($this, $amount, $cause));
        if($ev->isCancelled()){
            return 0.0;
        }

        $exhaustion = $this->getExhaustion();
        $exhaustion += $ev->getAmount();

        while($exhaustion >= 4.0){
            $exhaustion -= 4.0;

            $saturation = $this->getSaturation();
            if($saturation > 0){
                $saturation = max(0, $saturation - 1.0);
                $this->setSaturation($saturation);
            }else{
                $food = $this->getFood();
                if($food > 0){
                    $food--;
                    $this->setFood($food);
                }
            }
        }
        $this->setExhaustion($exhaustion);

        return $ev->getAmount();
    }
    public function getXpLevel() : int{
        return (int) $this->attributeMap->getAttribute(Attribute::EXPERIENCE_LEVEL)->getValue();
    }
    public function setXpLevel(int $level) : bool{
        $this->server->getPluginManager()->callEvent($ev = new PlayerExperienceChangeEvent($this, $level, $this->getXpProgress()));
        if(!$ev->isCancelled()){
            $this->attributeMap->getAttribute(Attribute::EXPERIENCE_LEVEL)->setValue($level);
            return true;
        }
        return false;
    }
    public function addXpLevel(int $level) : bool{
        $oldxp = (int) $this->getXpLevel();
        $xp = $oldxp + $level;
        return $this->setXpLevel($xp);
    }
    public function takeXpLevel(int $level) : bool{
        $oldxp = (int) $this->getXpLevel();
        $xp = $oldxp - $level;
        return $this->setXpLevel($xp);
    }
    public function getXpProgress() : float{
        return $this->attributeMap->getAttribute(Attribute::EXPERIENCE)->getValue();
    }
    public function setXpProgress(float $progress) : bool{
        $this->attributeMap->getAttribute(Attribute::EXPERIENCE)->setValue($progress);
        return true;
    }
    public function getTotalXp() : int{
        return $this->totalXp;
    }
    public function setTotalXp(int $xp, bool $syncLevel = false) : bool{
        $xp &= 0x7fffffff;
        if($xp === $this->totalXp){
            return false;
        }
        if(!$syncLevel){
            $level = $this->getXpLevel();
            $diff = $xp - $this->totalXp + $this->getFilledXp();
            if($diff > 0){
                while($diff > ($v = self::getLevelXpRequirement($level))){
                    $diff -= $v;
                    if(++$level >= 21863){
                        $diff = $v;
                        break;
                    }
                }
            }else{
                while($diff < ($v = self::getLevelXpRequirement($level - 1))){
                    $diff += $v;
                    if(--$level <= 0){
                        $diff = 0;
                        break;
                    }
                }
            }
            $progress = ($diff / $v);
        }else{
            $values = self::getLevelFromXp($xp);
            $level = $values[0];
            $progress = $values[1];
        }

        $this->server->getPluginManager()->callEvent($ev = new PlayerExperienceChangeEvent($this, $level, $progress));
        if(!$ev->isCancelled()){
            $this->totalXp = $xp;
            $this->setXpLevel($ev->getExpLevel());
            $this->setXpProgress($ev->getProgress());
            return true;
        }
        return false;
    }
    public function addXp(int $xp, bool $syncLevel = false) : bool{
        return $this->setTotalXp($this->totalXp + $xp, $syncLevel);
    }
    public function takeXp(int $xp, bool $syncLevel = false) : bool{
        return $this->setTotalXp($this->totalXp - $xp, $syncLevel);
    }
    public function getRemainderXp() : int{
        return self::getLevelXpRequirement($this->getXpLevel()) - $this->getFilledXp();
    }
    public function getFilledXp() : int{
        return self::getLevelXpRequirement($this->getXpLevel()) * $this->getXpProgress();
    }
    public function recalculateXpProgress() : float{
        $this->setXpProgress($progress = $this->getRemainderXp() / self::getLevelXpRequirement($this->getXpLevel()));
        return $progress;
    }
    public function getXpSeed() : int{
        return $this->xpSeed;
    }

    public function resetXpCooldown(){
        $this->xpCooldown = microtime(true);
    }
    public function canPickupXp() : bool{
        return microtime(true) - $this->xpCooldown > 0.5;
    }
    public static function getTotalXpRequirement(int $level) : int{
        if($level <= 16){
            return ($level ** 2) + (6 * $level);
        }elseif($level <= 31){
            return (2.5 * ($level ** 2)) - (40.5 * $level) + 360;
        }elseif($level <= 21863){
            return (4.5 * ($level ** 2)) - (162.5 * $level) + 2220;
        }
        return PHP_INT_MAX;
    }
    public static function getLevelXpRequirement(int $level) : int{
        if($level <= 16){
            return (2 * $level) + 7;
        }elseif($level <= 31){
            return (5 * $level) - 38;
        }elseif($level <= 21863){
            return (9 * $level) - 158;
        }
        return PHP_INT_MAX;
    }
    public static function getLevelFromXp(int $xp) : array{
        $xp &= 0x7fffffff;
        $a = 1;
        $b = 6;
        $c = -$xp;
        if($xp > self::getTotalXpRequirement(16)){
            if($xp <= self::getTotalXpRequirement(31)){
                $a = 2.5;
                $b = -40.5;
                $c += 360;
            }else{
                $a = 4.5;
                $b = -162.5;
                $c += 2220;
            }
        }

        $answer = max(Math::solveQuadratic($a, $b, $c));
        $level = floor($answer);
        $progress = $answer - $level;
        return [$level, $progress];
    }
    public function getInventory(){
        return $this->inventory;
    }
    public function getEnderChestInventory(){
        return $this->enderChestInventory;
    }
    public function getFloatingInventory(){
        return $this->floatingInventory;
    }
    public function getOffhandInventory() : OffhandInventory{
        return $this->offhandInventory;
    }
    public function getTransactionQueue() : SimpleTransactionQueue{
        if($this->transactionQueue === null){
            $this->transactionQueue = new SimpleTransactionQueue($this);
        }
        return $this->transactionQueue;
    }
    protected function initHumanData() : void{
        if(isset($this->namedtag->NameTag)){
            $this->setNameTag($this->namedtag["NameTag"]);
        }

        if(isset($this->namedtag->Skin) and $this->namedtag->Skin instanceof CompoundTag){
            $this->setSkin($this->namedtag->Skin["Data"], $this->namedtag->Skin["Name"]);
        }

        $this->uuid = UUID::fromData((string) $this->getId(), $this->getSkinData(), $this->getNameTag());
    }

    protected function initEntity(){
        $this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, false, self::DATA_TYPE_BYTE);
        $this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [0, 0, 0]);

        $inventoryContents = ($this->namedtag->Inventory ?? null);
        $this->inventory = new PlayerInventory($this, $inventoryContents);
        $this->enderChestInventory = new EnderChestInventory($this, ($this->namedtag->EnderChestInventory ?? null));
        $this->offhandInventory = new OffhandInventory($this);

        $this->floatingInventory = new FloatingInventory($this);

        $this->initHumanData();

        if(isset($this->namedtag->OffHandItem) && $this->namedtag->OffHandItem instanceof CompoundTag){
            if($this->offhandInventory === null)
                $this->offhandInventory = new OffhandInventory($this);

            $this->offhandInventory->setItemInOffhand(ItemItem::nbtDeserialize($this->namedtag->OffHandItem));
        }

        parent::initEntity();

        if(!isset($this->namedtag->foodLevel) or !($this->namedtag->foodLevel instanceof IntTag)){
            $this->namedtag->foodLevel = new IntTag("foodLevel", $this->getFood());
        }else{
            $this->setFood((float) $this->namedtag["foodLevel"]);
        }

        if(!isset($this->namedtag->foodExhaustionLevel) or !($this->namedtag->foodExhaustionLevel instanceof FloatTag)){
            $this->namedtag->foodExhaustionLevel = new FloatTag("foodExhaustionLevel", $this->getExhaustion());
        }else{
            $this->setExhaustion($this->namedtag["foodExhaustionLevel"]);
        }

        if(!isset($this->namedtag->foodSaturationLevel) or !($this->namedtag->foodSaturationLevel instanceof FloatTag)){
            $this->namedtag->foodSaturationLevel = new FloatTag("foodSaturationLevel", $this->getSaturation());
        }else{
            $this->setSaturation((float) $this->namedtag["foodSaturationLevel"]);
        }

        if(!isset($this->namedtag->foodTickTimer) or !($this->namedtag->foodTickTimer instanceof IntTag)){
            $this->namedtag->foodTickTimer = new IntTag("foodTickTimer", $this->foodTickTimer);
        }else{
            $this->foodTickTimer = $this->namedtag["foodTickTimer"];
        }

        if(!isset($this->namedtag->XpLevel) or !($this->namedtag->XpLevel instanceof IntTag)){
            $this->namedtag->XpLevel = new IntTag("XpLevel", 0);
        }
        $this->setXpLevel((int) $this->namedtag["XpLevel"]);

        if(!isset($this->namedtag->XpP) or !($this->namedtag->XpP instanceof FloatTag)){
            $this->namedtag->XpP = new FloatTag("XpP", 0);
        }
        $this->setXpProgress($this->namedtag["XpP"]);

        if(!isset($this->namedtag->XpTotal) or !($this->namedtag->XpTotal instanceof IntTag)){
            $this->namedtag->XpTotal = new IntTag("XpTotal", 0);
        }
        $this->totalXp = $this->namedtag["XpTotal"];

        if(!isset($this->namedtag->XpSeed) or !($this->namedtag->XpSeed instanceof IntTag)){
            $this->namedtag->XpSeed = new IntTag("XpSeed", random_int(INT32_MIN, INT32_MAX));
        }
        $this->xpSeed = $this->namedtag["XpSeed"];
    }

    protected function addAttributes() : void{
        parent::addAttributes();

        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::SATURATION));
        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXHAUSTION));
        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HUNGER));
        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE_LEVEL));
        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE));
        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HEALTH));
        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::MOVEMENT_SPEED));
        $this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::ABSORPTION));
    }
    public function entityBaseTick($tickDiff = 1, $EnchantL = 0){
        if($this->getInventory() instanceof PlayerInventory){
            $EnchantL = $this->getInventory()->getHelmet()->getEnchantmentLevel(Enchantment::TYPE_WATER_BREATHING);
        }
        $hasUpdate = parent::entityBaseTick($tickDiff, $EnchantL);

        $this->doFoodTick($tickDiff);

        return $hasUpdate;
    }

    public function applyDamageModifiers(EntityDamageEvent $source) : void{
        parent::applyDamageModifiers($source);

        $type = $source->getCause();
        if($type !== EntityDamageEvent::CAUSE_VOID
            && ($this->getInventory()->getItemInHand() instanceof Totem || $this->getOffhandInventory()->getItemInOffhand() instanceof Totem)){

            $compensation = $this->getHealth() - $source->getFinalDamage() - 1;
            if($compensation <= -1){
                $source->setDamage($compensation, EntityDamageEvent::MODIFIER_TOTEM);
            }
        }
    }

    protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
        parent::applyPostDamageEffects($source);
        $totemModifier = $source->getDamage(EntityDamageEvent::MODIFIER_TOTEM);
        if($totemModifier < 0){
            $event = new EntityConsumeTotemEvent($this);
            $this->server->getPluginManager()->callEvent($event);

            $pk = new EntityEventPacket();
            $pk->entityRuntimeId = $this->id;
            $pk->event = EntityEventPacket::CONSUME_TOTEM;

            $viewers = $this->getViewers();
            if($this instanceof Player){
                $viewers = array_merge($viewers, [$this]);
            }
            $this->server->batchPackets($viewers, [$pk]);

            $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_TOTEM);

            $this->removeAllEffects();

            $this->setHealth(1);

            $this->addEffect(Effect::getEffect(Effect::REGENERATION)->setDuration(20 * 40)->setAmplifier(1));
            $this->addEffect(Effect::getEffect(Effect::ABSORPTION)->setDuration(20 * 5)->setAmplifier(1));
            $this->addEffect(Effect::getEffect(Effect::FIRE_RESISTANCE)->setDuration(20 * 40)->setAmplifier(0));

            $hand = $this->inventory->getItemInHand();
            if($hand instanceof Totem){
                $hand->pop();
                $this->getInventory()->setItemInHand(ItemItem::get(ItemIds::AIR));
				$this->getInventory()->sendContents($this);
            }elseif(($offHand = $this->getOffhandInventory()->getItemInOffhand()) instanceof Totem){
                $offHand->pop();
                $this->getOffhandInventory()->setItemInOffhand(ItemItem::get(ItemIds::AIR));
				$this->getOffhandInventory()->sendContents($this);
            }
        }
    }

    public function doFoodTick($tickDiff = 1){
        if($this->isAlive()){
            $food = $this->getFood();
            $health = $this->getHealth();
            $difficulty = $this->level->getDifficulty();

            $this->foodTickTimer += $tickDiff;
            if($this->foodTickTimer >= 80){
                $this->foodTickTimer = 0;
            }

            if($difficulty === Level::DIFFICULTY_PEACEFUL and $this->foodTickTimer % 10 === 0){
                if($food < $this->getMaxFood()){
                    $this->addFood(1.0);
                    $food = $this->getFood();
                }
                if($this->foodTickTimer % 20 === 0 && $health < $this->getMaxHealth()){
                    $this->heal(new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_SATURATION));
                }
            }

            if($this->foodTickTimer === 0){
                if($food >= 18){
                    if($health < $this->getMaxHealth()){
                        $this->heal(new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_SATURATION));
                        $this->exhaust(3.0, PlayerExhaustEvent::CAUSE_HEALTH_REGEN);
                    }
                }elseif($food <= 0){
                    if(($difficulty === Level::DIFFICULTY_EASY && $health > 10) || ($difficulty === Level::DIFFICULTY_NORMAL && $health > 1) || $difficulty === Level::DIFFICULTY_HARD){
                        $this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_STARVATION, 1));
                    }
                }
            }

            if($food <= 6){
                $this->setSprinting(false);
            }
        }
    }
    public function getName(){
        return $this->getNameTag();
    }
    public function getDrops(){
        $drops = [];
        if($this->inventory !== null){
            foreach($this->inventory->getContents() as $item){
                $drops[] = $item;
            }
            foreach($this->inventory->getArmorContents() as $armor){
                $drops[] = $armor;
            }
        }

        return $drops;
    }

    public function saveNBT(){
        parent::saveNBT();

        if($this->offhandInventory !== null){
            $this->namedtag->OffHandItem = $this->getOffhandInventory()->getItemInOffhand()->nbtSerialize(0, "OffHandItem");
        }

        $this->namedtag->Inventory = new ListTag("Inventory", []);
        $this->namedtag->Inventory->setTagType(NBT::TAG_Compound);
        if($this->inventory !== null){

            for($slot = 0; $slot < $this->inventory->getHotbarSize(); ++$slot){
                $inventorySlotIndex = $this->inventory->getHotbarSlotIndex($slot);
                $item = $this->inventory->getItem($inventorySlotIndex);
                $tag = $item->nbtSerialize($slot);
                $tag->TrueSlot = new ByteTag("TrueSlot", $inventorySlotIndex);
                $this->namedtag->Inventory[$slot] = $tag;
            }

            $slotCount = $this->inventory->getSize() + $this->inventory->getHotbarSize();
            for($slot = $this->inventory->getHotbarSize(); $slot < $slotCount; ++$slot){
                $item = $this->inventory->getItem($slot - $this->inventory->getHotbarSize());
                if($item->getId() !== ItemItem::AIR){
                    $this->namedtag->Inventory[$slot] = $item->nbtSerialize($slot);
                }
            }

            for($slot = 100; $slot < 104; ++$slot){
                $item = $this->inventory->getItem($this->inventory->getSize() + $slot - 100);
                if($item instanceof ItemItem and $item->getId() !== ItemItem::AIR){
                    $this->namedtag->Inventory[$slot] = $item->nbtSerialize($slot);
                }
            }
        }

        $this->namedtag->EnderChestInventory = new ListTag("EnderChestInventory", []);
        $this->namedtag->Inventory->setTagType(NBT::TAG_Compound);
        if($this->enderChestInventory !== null){
            for($slot = 0; $slot < $this->enderChestInventory->getSize(); $slot++){
                if(($item = $this->enderChestInventory->getItem($slot)) instanceof ItemItem){
                    $this->namedtag->EnderChestInventory[$slot] = $item->nbtSerialize($slot);
                }
            }
        }

        if(strlen($this->getSkinData()) > 0){
            $this->namedtag->Skin = new CompoundTag("Skin", [
                new ByteArrayTag("Data", $this->getSkinData()),
                new StringTag("Name", $this->getSkinId())
            ]);
        }

        $this->namedtag->XpLevel = new IntTag("XpLevel", $this->getXpLevel());
        $this->namedtag->XpTotal = new IntTag("XpTotal", $this->getTotalXp());
        $this->namedtag->XpP = new FloatTag("XpP", $this->getXpProgress());
        $this->namedtag->XpSeed = new IntTag("XpSeed", $this->getXpSeed());

        $this->namedtag->foodLevel = new IntTag("foodLevel", $this->getFood());
        $this->namedtag->foodExhaustionLevel = new FloatTag("foodExhaustionLevel", $this->getExhaustion());
        $this->namedtag->foodSaturationLevel = new FloatTag("foodSaturationLevel", $this->getSaturation());
        $this->namedtag->foodTickTimer = new IntTag("foodTickTimer", $this->foodTickTimer);
    }
    public function spawnTo(Player $player){
        if(strlen($this->skin) < 64 * 32 * 4){
            $e = new InvalidStateException((new ReflectionClass($this))->getShortName() . " must have a valid skin set");
            $this->server->getLogger()->logException($e);
            $this->close();
        }elseif($player !== $this and !isset($this->hasSpawned[$player->getLoaderId()])){
            $this->hasSpawned[$player->getLoaderId()] = $player;

            if(!($this instanceof Player)){
                $this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getName(), $this->skinId, $this->skin, [$player]);
            }

            $pk = new AddPlayerPacket();
            $pk->uuid = $this->getUniqueId();
            $pk->username = $this->getName();
            $pk->entityRuntimeId = $this->getId();
            $pk->x = $this->x;
            $pk->y = $this->y;
            $pk->z = $this->z;
            $pk->speedX = $this->motion->x;
            $pk->speedY = $this->motion->y;
            $pk->speedZ = $this->motion->z;
            $pk->yaw = $this->yaw;
            $pk->pitch = $this->pitch;
            $pk->item = $this->getInventory()->getItemInHand();
            $pk->metadata = $this->dataProperties;
            $player->dataPacket($pk);

            $this->sendLinkedData();

            $this->inventory->sendArmorContents($player);
            $this->offhandInventory->sendContents($player);

            if(!($this instanceof Player)){
                $this->server->removePlayerListData($this->getUniqueId(), [$player]);
            }
        }
    }

    public function close(){
        if(!$this->closed){
            $this->constructed = false;

            if($this->getFloatingInventory() instanceof FloatingInventory){
                if($this->getInventory() instanceof Inventory){
                    foreach($this->getFloatingInventory()->getContents() as $craftingItem){
                        $this->inventory->addItem($craftingItem);
                    }
                }
            }else{
                $this->server->getLogger()->debug("Attempted to drop a null crafting inventory\n");
            }
            if($this->inventory !== null){
                $this->inventory->removeAllViewers(true);
                $this->inventory = null;
            }
            if($this->enderChestInventory !== null){
                $this->enderChestInventory->removeAllViewers(true);
                $this->enderChestInventory = null;
            }
            if($this->offhandInventory !== null){
                $this->offhandInventory->removeAllViewers(true);
                $this->offhandInventory = null;
            }
            parent::close();
        }
    }
}
