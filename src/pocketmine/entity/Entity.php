<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\entity;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Fire;
use pocketmine\block\Portal;
use pocketmine\block\SlimeBlock;
use pocketmine\block\Water;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\item\Elytra;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Binary;
use ReflectionClass;
use ReflectionException;

abstract class Entity extends Location implements Metadatable{

	public const MOTION_THRESHOLD = 0.00001;

	protected const STEP_CLIP_MULTIPLIER = 0.4;

	const NETWORK_ID = -1;

	const DATA_TYPE_BYTE = 0;
	const DATA_TYPE_SHORT = 1;
	const DATA_TYPE_INT = 2;
	const DATA_TYPE_FLOAT = 3;
	const DATA_TYPE_STRING = 4;
	const DATA_TYPE_SLOT = 5;
	const DATA_TYPE_POS = 6;
	const DATA_TYPE_LONG = 7;
	const DATA_TYPE_VECTOR3F = 8;

	const DATA_FLAGS = 0;
	const DATA_HEALTH = 1;
	const DATA_VARIANT = 2;
	const DATA_COLOR = 3, DATA_COLOUR = 3;
	const DATA_NAMETAG = 4;
	const DATA_OWNER_EID = 5;
	const DATA_TARGET_EID = 6;
	const DATA_AIR = 7;
	const DATA_POTION_COLOR = 8;
	const DATA_POTION_AMBIENT = 9;
	/* 10 (byte) */
	const DATA_HURT_TIME = 11;
	const DATA_HURT_DIRECTION = 12;
	const DATA_PADDLE_TIME_LEFT = 13;
	const DATA_PADDLE_TIME_RIGHT = 14;
	const DATA_EXPERIENCE_VALUE = 15;
	const DATA_MINECART_DISPLAY_BLOCK = 16;
	const DATA_MINECART_DISPLAY_OFFSET = 17;
	const DATA_MINECART_HAS_DISPLAY = 18;


	const DATA_ENDERMAN_HELD_ITEM_ID = 23;
	const DATA_ENDERMAN_HELD_ITEM_DAMAGE = 24;
	const DATA_ENTITY_AGE = 25;

	/* 27 (byte) player-specific flags
	 * 28 (int) player "index"?
	 * 29 (block coords) bed position */
	const DATA_FIREBALL_POWER_X = 30;
	const DATA_FIREBALL_POWER_Y = 31;
	const DATA_FIREBALL_POWER_Z = 32;
	/* 33 (unknown)
	 * 34 (float) fishing bobber
	 * 35 (float) fishing bobber
	 * 36 (float) fishing bobber */
	const DATA_POTION_AUX_VALUE = 37;
	const DATA_LEAD_HOLDER_EID = 38;
	const DATA_SCALE = 39;
	const DATA_INTERACTIVE_TAG = 40;
	const DATA_NPC_SKIN_ID = 41;
	const DATA_URL_TAG = 42;
	const DATA_MAX_AIR = 43;
	const DATA_MARK_VARIANT = 44;
	/* 45 (byte) container stuff
	 * 46 (int) container stuff
	 * 47 (int) container stuff */
	const DATA_BLOCK_TARGET = 48;
	const DATA_WITHER_INVULNERABLE_TICKS = 49;
	const DATA_WITHER_TARGET_1 = 50;
	const DATA_WITHER_TARGET_2 = 51;
	const DATA_WITHER_TARGET_3 = 52;
	/* 53 (short) */
	const DATA_BOUNDING_BOX_WIDTH = 54;
	const DATA_BOUNDING_BOX_HEIGHT = 55;
	const DATA_FUSE_LENGTH = 56;
	const DATA_RIDER_SEAT_POSITION = 57;
	const DATA_RIDER_ROTATION_LOCKED = 58;
	const DATA_RIDER_MAX_ROTATION = 59;
	const DATA_RIDER_MIN_ROTATION = 60;
	const DATA_AREA_EFFECT_CLOUD_RADIUS = 61;
	const DATA_AREA_EFFECT_CLOUD_WAITING = 62;
	const DATA_AREA_EFFECT_CLOUD_PARTICLE_ID = 63;
	/* 64 (int) shulker-related */
	const DATA_SHULKER_ATTACH_FACE = 65;
	/* 66 (short) shulker-related */
	const DATA_SHULKER_ATTACH_POS = 67;
	const DATA_TRADING_PLAYER_EID = 68;

	/* 70 (byte) command-block */
	const DATA_COMMAND_BLOCK_COMMAND = 71;
	const DATA_COMMAND_BLOCK_LAST_OUTPUT = 72;
	const DATA_COMMAND_BLOCK_TRACK_OUTPUT = 73;
	const DATA_CONTROLLING_RIDER_SEAT_NUMBER = 74;
	const DATA_STRENGTH = 75;
	const DATA_MAX_STRENGTH = 76;
	/* 77 (int) */
	const DATA_ARMOR_STAND_POSE_INDEX = 78;
	const DATA_ENDER_CRYSTAL_TIME_OFFSET = 79;
	const DATA_FLAGS2 = 91;


	const DATA_FLAG_ONFIRE = 0;
	const DATA_FLAG_SNEAKING = 1;
	const DATA_FLAG_RIDING = 2;
	const DATA_FLAG_SPRINTING = 3;
	const DATA_FLAG_ACTION = 4;
	const DATA_FLAG_INVISIBLE = 5;
	const DATA_FLAG_TEMPTED = 6;
	const DATA_FLAG_INLOVE = 7;
	const DATA_FLAG_SADDLED = 8;
	const DATA_FLAG_POWERED = 9;
	const DATA_FLAG_IGNITED = 10;
	const DATA_FLAG_BABY = 11;
	const DATA_FLAG_CONVERTING = 12;
	const DATA_FLAG_CRITICAL = 13;
	const DATA_FLAG_CAN_SHOW_NAMETAG = 14;
	const DATA_FLAG_ALWAYS_SHOW_NAMETAG = 15;
	const DATA_FLAG_IMMOBILE = 16, DATA_FLAG_NO_AI = 16;
	const DATA_FLAG_SILENT = 17;
	const DATA_FLAG_WALLCLIMBING = 18;
	const DATA_FLAG_CAN_CLIMB = 19;
	const DATA_FLAG_SWIMMER = 20;
	const DATA_FLAG_CAN_FLY = 21;
	const DATA_FLAG_RESTING = 22;
	const DATA_FLAG_SITTING = 23;
	const DATA_FLAG_ANGRY = 24;
	const DATA_FLAG_INTERESTED = 25;
	const DATA_FLAG_CHARGED = 26;
	const DATA_FLAG_TAMED = 27;
	const DATA_FLAG_LEASHED = 28;
	const DATA_FLAG_SHEARED = 29;
	const DATA_FLAG_GLIDING = 30;
	const DATA_FLAG_ELDER = 31;
	const DATA_FLAG_MOVING = 32;
	const DATA_FLAG_BREATHING = 33;
	const DATA_FLAG_CHESTED = 34;
	const DATA_FLAG_STACKABLE = 35;
	const DATA_FLAG_SHOWBASE = 36;
	const DATA_FLAG_REARING = 37;
	const DATA_FLAG_VIBRATING = 38;
	const DATA_FLAG_IDLING = 39;
	const DATA_FLAG_EVOKER_SPELL = 40;
	const DATA_FLAG_CHARGE_ATTACK = 41;
	const DATA_FLAG_WASD_CONTROLLED = 43;
	const DATA_FLAG_CAN_POWER_JUMP = 44;
	const DATA_FLAG_LINGER = 45;

	const SOUTH = 0;
	const WEST = 1;
	const NORTH = 2;
	const EAST = 3;

	public static $entityCount = 1;
	private static $knownEntities = [];
	private static $shortNames = [];
	public static function init(){
		Entity::registerEntity(Arrow::class);
		Entity::registerEntity(Bat::class);
		Entity::registerEntity(Blaze::class);
		Entity::registerEntity(Boat::class);
		Entity::registerEntity(CaveSpider::class);
		Entity::registerEntity(Chicken::class);
		Entity::registerEntity(Cow::class);
		Entity::registerEntity(Creeper::class);
		Entity::registerEntity(Donkey::class);
		Entity::registerEntity(DroppedItem::class);
		Entity::registerEntity(Egg::class);
		Entity::registerEntity(ElderGuardian::class);
		Entity::registerEntity(Enderman::class);
		Entity::registerEntity(Endermite::class);
		Entity::registerEntity(EnderDragon::class);
		Entity::registerEntity(EnderPearl::class);
		Entity::registerEntity(Evoker::class);
		Entity::registerEntity(FallingSand::class);
		Entity::registerEntity(FishingHook::class);
		Entity::registerEntity(Ghast::class);
		Entity::registerEntity(Guardian::class);
		Entity::registerEntity(Horse::class);
		Entity::registerEntity(Husk::class);
		Entity::registerEntity(IronGolem::class);
		Entity::registerEntity(LavaSlime::class);
		Entity::registerEntity(Lightning::class);
		Entity::registerEntity(Llama::class);
		Entity::registerEntity(Minecart::class);
		Entity::registerEntity(MinecartChest::class);
		Entity::registerEntity(MinecartHopper::class);
		Entity::registerEntity(MinecartTNT::class);
		Entity::registerEntity(Mooshroom::class);
		Entity::registerEntity(Mule::class);
		Entity::registerEntity(Ocelot::class);
		Entity::registerEntity(Painting::class);
		Entity::registerEntity(Pig::class);
		Entity::registerEntity(PigZombie::class);
		Entity::registerEntity(PolarBear::class);
		Entity::registerEntity(PrimedTNT::class);
		Entity::registerEntity(Rabbit::class);
		Entity::registerEntity(Sheep::class);
		Entity::registerEntity(Shulker::class);
		Entity::registerEntity(Silverfish::class);
		Entity::registerEntity(Skeleton::class);
		Entity::registerEntity(SkeletonHorse::class);
		Entity::registerEntity(Slime::class);
		Entity::registerEntity(Snowball::class);
		Entity::registerEntity(SnowGolem::class);
		Entity::registerEntity(Spider::class);
		Entity::registerEntity(Squid::class);
		Entity::registerEntity(Stray::class);
		Entity::registerEntity(ThrownExpBottle::class);
		Entity::registerEntity(ThrownPotion::class);
		Entity::registerEntity(Vex::class);
		Entity::registerEntity(Villager::class);
		Entity::registerEntity(Vindicator::class);
		Entity::registerEntity(Witch::class);
		Entity::registerEntity(Wither::class);
		Entity::registerEntity(WitherSkeleton::class);
		Entity::registerEntity(Wolf::class);
		Entity::registerEntity(XPOrb::class);
		Entity::registerEntity(Zombie::class);
		Entity::registerEntity(ZombieHorse::class);
		Entity::registerEntity(ZombieVillager::class);
		Entity::registerEntity(WitherTNT::class);
		Entity::registerEntity(EnderCrystal::class);

		Entity::registerEntity(Human::class, true);

		Attribute::init();
		Effect::init();
	}
	protected $hasSpawned = [];
	protected $effects = [];

	protected $id = -1;

	protected $dataProperties = [
		self::DATA_FLAGS => [self::DATA_TYPE_LONG, 0],
		self::DATA_AIR => [self::DATA_TYPE_SHORT, 400],
		self::DATA_MAX_AIR => [self::DATA_TYPE_SHORT, 400],
		self::DATA_NAMETAG => [self::DATA_TYPE_STRING, ""],
		self::DATA_LEAD_HOLDER_EID => [self::DATA_TYPE_LONG, -1],
		self::DATA_SCALE => [self::DATA_TYPE_FLOAT, 1]
	];

	protected $changedDataProperties = [];

	public $passenger = null;
	public $vehicle = null;
	public $chunk;

	protected $lastDamageCause = null;
	protected $blocksAround = null;
	public $lastX;
	public $lastY;
	public $lastZ;
	protected $motion;
	public $temporalVector;
	protected $lastMotion;
	protected $forceMovementUpdate = false;

	public $lastYaw;
	public $lastPitch;
	public $boundingBox;
	public $onGround;
	public $inBlock = false;
	public $positionChanged;
	public $motionChanged;
	public $deadTicks = 0;
	protected $maxDeadTicks = 0;

	public $height;

	public $eyeHeight = null;

	public $width;
	protected $baseOffset = 0.0;
	private $savedWithChunk = true;
	private $health = 20.0;
	private $maxHealth = 20;

	protected $ySize = 0;
	protected $stepHeight = 0;
	public $keepMovement = false;

	public $fallDistance = 0.0;
	public $ticksLived = 0;
	public $lastUpdate;
	public $fireTicks = 0;
	public $namedtag;
	public $canCollide = true;

	public $isCollided = false;
	public $isCollidedHorizontally = false;
	public $isCollidedVertically = false;

	public $noDamageTicks;
	protected $justCreated = true;
	private $invulnerable;
	protected $attributeMap;

	protected $gravity;
	protected $drag;
	protected $server;
	public $closed = false;
	private $needsDespawn = false;
	protected $timings;
	protected $isPlayer = false;
	protected $linkedEntity = null;
	protected $linkedType = null;


	protected $riding = null;

	public $dropExp = [0, 0];
	protected $constructed = false;
	private $closeInFlight = false;

	public function __construct(Level $level, CompoundTag $nbt){
		$this->constructed = true;
		$this->timings = Timings::getEntityTimings($this);

		$this->isPlayer = $this instanceof Player;

		$this->temporalVector = new Vector3(0, 0, 0);

		if($this->eyeHeight === null){
			$this->eyeHeight = $this->height / 2 + 0.1;
		}

		$this->id = Entity::$entityCount++;
		$this->namedtag = $nbt;
		$this->server = $level->getServer();

		parent::__construct($this->namedtag["Pos"][0], $this->namedtag["Pos"][1], $this->namedtag["Pos"][2], $this->namedtag->Rotation[0], $this->namedtag->Rotation[1], $level);
		assert(!is_nan($this->x) and !is_infinite($this->x) and !is_nan($this->y) and !is_infinite($this->y) and !is_nan($this->z) and !is_infinite($this->z));

		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		$this->recalculateBoundingBox();

		$this->chunk = $level->getChunkAtPosition($this, true);
		if($this->chunk === null){
			throw new \InvalidStateException("Cannot create entities in unloaded chunks");
		}

		$this->motion = new Vector3(0, 0, 0);
		if(isset($this->namedtag->Motion)){
			$this->setMotion($this->temporalVector->setComponents($this->namedtag["Motion"][0], $this->namedtag["Motion"][1], $this->namedtag["Motion"][2]));
		}else{
			$this->setMotion($this->temporalVector->setComponents(0, 0, 0));
		}

		$this->resetLastMovements();

		if(!isset($this->namedtag->FallDistance)){
			$this->namedtag->FallDistance = new FloatTag("FallDistance", 0.0);
		}
		$this->fallDistance = $this->namedtag["FallDistance"];

		if(!isset($this->namedtag->Fire)){
			$this->namedtag->Fire = new ShortTag("Fire", 0);
		}
		$this->fireTicks = $this->namedtag["Fire"];
		if($this->isOnFire()){
			$this->setGenericFlag(self::DATA_FLAG_ONFIRE);
		}

		if(!isset($this->namedtag->Air)){
			$this->namedtag->Air = new ShortTag("Air", 300);
		}
		$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $this->namedtag["Air"]);

		if(!isset($this->namedtag->OnGround)){
			$this->namedtag->OnGround = new ByteTag("OnGround", 0);
		}
		$this->onGround = $this->namedtag["OnGround"] > 0;

		if(!isset($this->namedtag->Invulnerable)){
			$this->namedtag->Invulnerable = new ByteTag("Invulnerable", 0);
		}
		$this->invulnerable = $this->namedtag["Invulnerable"] > 0;

		$this->attributeMap = new AttributeMap();
		$this->addAttributes();

		$this->initEntity();

		$this->chunk->addEntity($this);
		$this->level->addEntity($this);

		$this->lastUpdate = $this->server->getTick();

		$this->scheduleUpdate();
	}

	public static function createBaseNBT(Vector3 $position, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag{
		return new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $position->x),
				new DoubleTag("", $position->y),
				new DoubleTag("", $position->z)
			]),
			new ListTag("Motion", [
                new DoubleTag("", $motion !== null ? $motion->x : 0.0),
                new DoubleTag("", $motion !== null ? $motion->y : 0.0),
                new DoubleTag("", $motion !== null ? $motion->z : 0.0)
			]),
			new ListTag("Rotation", [
				new FloatTag("", $yaw),
				new FloatTag("", $pitch),
			]),
		]);
	}
	public function getHeight(){
		return $this->height;
	}
	public function getWidth(){
		return $this->width;
	}
	public function setScale(float $value) : void{
		if($value <= 0){
			throw new InvalidArgumentException("Scale must be greater than 0");
		}
		$multiplier = $value / $this->getScale();

		$this->width *= $multiplier;
		$this->height *= $multiplier;
		$this->eyeHeight *= $multiplier;

		$this->recalculateBoundingBox();

		$this->setDataProperty(self::DATA_SCALE, self::DATA_TYPE_FLOAT, $value);
	}
	public function getScale(){
		return $this->getDataProperty(self::DATA_SCALE);
	}
	public function getDropExpMin() : int{
		return $this->dropExp[0];
	}
	public function getDropExpMax() : int{
		return $this->dropExp[1];
	}

	public function getNameTag() : string{
		return $this->getDataProperty(self::DATA_NAMETAG);
	}

	public function isNameTagVisible() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_SHOW_NAMETAG);
	}

	public function isNameTagAlwaysVisible() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ALWAYS_SHOW_NAMETAG);
	}

	public function setNameTag(string $name){
		$this->setDataProperty(self::DATA_NAMETAG, self::DATA_TYPE_STRING, $name);
	}

	public function setNameTagVisible(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_SHOW_NAMETAG, $value);
	}

	public function setNameTagAlwaysVisible(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ALWAYS_SHOW_NAMETAG, $value);
	}

	public function isSneaking() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SNEAKING);
	}

	public function setSneaking(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SNEAKING, $value);
	}

	public function isSprinting() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SPRINTING);
	}

	public function setSprinting(bool $value = true){
		if($value !== $this->isSprinting()){
			$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SPRINTING, (bool) $value);
			$attr = $this->attributeMap->getAttribute(Attribute::MOVEMENT_SPEED);
			$attr->setValue($value ? ($attr->getValue() * 1.3) : ($attr->getValue() / 1.3), false, true);
		}
	}

	public function isGliding() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_GLIDING);
	}

	public function setGliding(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_GLIDING, (bool) $value);
	}

	public function isImmobile() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE);
	}

	public function setImmobile(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE, $value);
	}

	public function isInvisible() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_INVISIBLE);
	}

	public function setInvisible(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_INVISIBLE, $value);
	}
	public function canClimb() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB);
	}
	public function setCanClimb(bool $value){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB, $value);
	}
	public function canClimbWalls() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING);
	}
	public function setCanClimbWalls(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING, $value);
	}
	public function getOwningEntityId(){
		return $this->getDataProperty(self::DATA_OWNER_EID);
	}
	public function getOwningEntity(){
		$eid = $this->getOwningEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid);
		}

		return null;
	}
	public function setOwningEntity(Entity $owner) : bool{
		if($owner->closed){
			throw new InvalidArgumentException("Supplied owning entity is garbage and cannot be used");
		}

		$this->setDataProperty(self::DATA_OWNER_EID, self::DATA_TYPE_LONG, $owner->getId());
		return true;
	}
	public function getTargetEntityId(){
		return $this->getDataProperty(self::DATA_TARGET_EID);
	}
	public function getTargetEntity(){
		$eid = $this->getTargetEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid);
		}

		return null;
	}
	public function setTargetEntity(Entity $target){
		if($target->closed){
			throw new InvalidArgumentException("Supplied target entity is garbage and cannot be used");
		}

		$this->setDataProperty(self::DATA_TARGET_EID, self::DATA_TYPE_LONG, $target->getId());
	}
	public function getEffects(){
		return $this->effects;
	}

	public function removeAllEffects(){
		foreach($this->effects as $effect){
			$this->removeEffect($effect->getId());
		}
	}
	public function removeEffect($effectId){
		if(isset($this->effects[$effectId])){
			$effect = $this->effects[$effectId];

			unset($this->effects[$effectId]);
			$effect->remove($this);

			$this->recalculateEffectColor();

			return true;
		}

		return false;
	}
	public function getEffect($effectId){
		return $this->effects[$effectId] ?? null;
	}
	public function hasEffect($effectId){
		return isset($this->effects[$effectId]);
	}
	public function addEffect(Effect $effect){
		if(isset($this->effects[$effect->getId()])){
			$oldEffect = $this->effects[$effect->getId()];
			if(
				abs($effect->getAmplifier()) <= ($oldEffect->getAmplifier())
				or (abs($effect->getAmplifier()) === abs($oldEffect->getAmplifier())
					and $effect->getDuration() < $oldEffect->getDuration())
			){
				return false;
			}
			$effect->add($this, true, $oldEffect);
		}else{
			$effect->add($this, false);
		}

		$this->effects[$effect->getId()] = $effect;

		$this->recalculateEffectColor();

		return true;
	}

	protected function recalculateEffectColor(){
		$color = [0, 0, 0];
		$count = 0;
		$ambient = true;
		foreach($this->effects as $effect){
			if($effect->isVisible() and $effect->hasBubbles()){
				$c = $effect->getColor();
				$color[0] += $c[0] * $effect->getEffectLevel();
				$color[1] += $c[1] * $effect->getEffectLevel();
				$color[2] += $c[2] * $effect->getEffectLevel();
				$count += $effect->getEffectLevel();
				if(!$effect->isAmbient()){
					$ambient = false;
				}
			}
		}

		if($count > 0){
			$r = ($color[0] / $count) & 0xff;
			$g = ($color[1] / $count) & 0xff;
			$b = ($color[2] / $count) & 0xff;

			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, 0xff000000 | ($r << 16) | ($g << 8) | $b);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, $ambient ? 1 : 0);
		}else{
			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, 0);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, 0);
		}
	}
	public static function createEntity($type, Level $level, CompoundTag $nbt, ...$args){
		if(isset(self::$knownEntities[$type])){
			$class = self::$knownEntities[$type];

			return new $class($level, $nbt, ...$args);
		}

		return null;
	}
	public static function registerEntity(string $className, $force = false) : bool{
		$class = new ReflectionClass($className);
		if(is_a($className, Entity::class, true) and !$class->isAbstract()){
			if($className::NETWORK_ID !== -1){
				self::$knownEntities[$className::NETWORK_ID] = $className;
			}elseif(!$force){
				return false;
			}

			self::$knownEntities[$class->getShortName()] = $className;
			self::$shortNames[$className] = $class->getShortName();

			return true;
		}

		return false;
	}
	public function getSaveId(){
		return self::$shortNames[static::class];
	}

	public function saveNBT(){
		if(!($this instanceof Player)){
			$this->namedtag->id = new StringTag("id", $this->getSaveId());

			if($this->getNameTag() !== ""){
				$this->namedtag->CustomName = new StringTag("CustomName", $this->getNameTag());
				$this->namedtag->CustomNameVisible = new ByteTag("CustomNameVisible", $this->isNameTagVisible() ? 1 : 0);
				$this->namedtag->CustomNameAlwaysVisible = new StringTag("CustomNameAlwaysVisible", $this->isNameTagAlwaysVisible());
			}else{
				unset($this->namedtag->CustomName);
				unset($this->namedtag->CustomNameVisible);
				unset($this->namedtag->CustomNameAlwaysVisible);
			}
		}

		$this->namedtag->Pos = new ListTag("Pos", [
            new DoubleTag("", $this->x),
            new DoubleTag("", $this->y),
            new DoubleTag("", $this->z)
		]);

		$this->namedtag->Motion = new ListTag("Motion", [
            new DoubleTag("", $this->motion->x),
            new DoubleTag("", $this->motion->y),
            new DoubleTag("", $this->motion->z)
		]);

		$this->namedtag->Rotation = new ListTag("Rotation", [
            new FloatTag("", $this->yaw),
            new FloatTag("", $this->pitch)
		]);

		$this->namedtag->FallDistance = new FloatTag("FallDistance", $this->fallDistance);
		$this->namedtag->Fire = new ShortTag("Fire", $this->fireTicks);
		$this->namedtag->Air = new ShortTag("Air", $this->getDataProperty(self::DATA_AIR));
		$this->namedtag->OnGround = new ByteTag("OnGround", $this->onGround ? 1 : 0);
		$this->namedtag->Invulnerable = new ByteTag("Invulnerable", $this->invulnerable ? 1 : 0);

		if(count($this->effects) > 0){
			$effects = [];
			foreach($this->effects as $effect){
                $effects[] = new CompoundTag("", [
					new ByteTag("Id", $effect->getId()),
					new ByteTag("Amplifier", Binary::signByte($effect->getAmplifier())),
					new IntTag("Duration", $effect->getDuration()),
					new ByteTag("Ambient", 0),
					new ByteTag("ShowParticles", $effect->isVisible() ? 1 : 0)
				]);
			}

			$this->namedtag->ActiveEffects = new ListTag("ActiveEffects", $effects);
		}else{
			unset($this->namedtag->ActiveEffects);
		}
	}

	protected function initEntity(){
		if(!($this->namedtag instanceof CompoundTag)){
			throw new InvalidArgumentException("Expecting CompoundTag, received " . get_class($this->namedtag));
		}

		if(isset($this->namedtag->CustomName)){
			$this->setNameTag($this->namedtag["CustomName"]);
			if(isset($this->namedtag->CustomNameVisible)){
				$this->setNameTagVisible($this->namedtag["CustomNameVisible"] > 0);
			}
			if(isset($this->namedtag->CustomNameAlwaysVisible)){
				$this->setNameTagAlwaysVisible($this->namedtag["CustomNameAlwaysVisible"] > 0);
			}
		}

		$this->addAttributes();

		if(isset($this->namedtag->ActiveEffects)){
			foreach($this->namedtag->ActiveEffects->getValue() as $e){
				$amplifier = Binary::unsignByte($e->Amplifier->getValue());

				$effect = Effect::getEffect($e["Id"]);
				if($effect === null){
					continue;
				}

				$effect->setAmplifier($amplifier)->setDuration($e["Duration"])->setVisible($e["ShowParticles"] > 0);

				$this->addEffect($effect);
			}
		}

	}

	protected function addAttributes(){
	}
	public function getViewers(){
		return $this->hasSpawned;
	}

	public function spawnTo(Player $player){
		if(
			!isset($this->hasSpawned[$player->getLoaderId()]) and
			$this->chunk !== null and
			$player->getLevel() === $this->level and
			isset($player->usedChunks[$chunkHash = Level::chunkHash($this->chunk->getX(), $this->chunk->getZ())]) and
			$player->usedChunks[$chunkHash] === true
		){
			$this->hasSpawned[$player->getLoaderId()] = $player;
		}
	}
	public function sendPotionEffects(Player $player){
		foreach($this->effects as $effect){
			$pk = new MobEffectPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->effectId = $effect->getId();
			$pk->amplifier = $effect->getAmplifier();
			$pk->particles = $effect->isVisible();
			$pk->duration = $effect->getDuration();
			$pk->eventId = MobEffectPacket::EVENT_ADD;

			$player->dataPacket($pk);
		}
	}
	public function sendData($player, ?array $data = null){
		if(!is_array($player)){
			$player = [$player];
		}

		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $this->getId();
        $pk->metadata = $data ?? $this->dataProperties;

		foreach($player as $p){
			if($p === $this){
				continue;
			}
			$p->dataPacket(clone $pk);
		}

		if($this instanceof Player){
			$this->dataPacket($pk);
		}
	}
	public function broadcastEntityEvent(int $eventId, ?int $eventData = null, ?array $players = null) : void{
		$pk = new EntityEventPacket();
		$pk->entityRuntimeId = $this->id;
		$pk->event = $eventId;
		$pk->data = $eventData ?? 0;

		$this->server->broadcastPacket($players ?? $this->getViewers(), $pk);
	}
	public function despawnFrom(Player $player, bool $send = true){
		if(isset($this->hasSpawned[$player->getLoaderId()])){
			if($send){
				$pk = new RemoveEntityPacket();
				$pk->entityRuntimeId = $this->id;
				$player->dataPacket($pk);
			}
			unset($this->hasSpawned[$player->getLoaderId()]);
		}
	}
	public function attack(EntityDamageEvent $source){
		if($this->hasEffect(Effect::FIRE_RESISTANCE)
			and ($source->getCause() === EntityDamageEvent::CAUSE_FIRE
				or $source->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK
				or $source->getCause() === EntityDamageEvent::CAUSE_LAVA)
		){
			$source->setCancelled();
		}

		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return false;
		}

		$this->setLastDamageCause($source);

		$damage = $source->getFinalDamage();

		$absorption = $this->getAbsorption();
		if($absorption > 0){
			if($absorption > $damage){
				$this->setAbsorption($absorption - $damage);
				$damage = 0;
			}else{
				$this->setAbsorption(0);
				$damage -= $absorption;
			}
		}

		$this->setHealth($this->getHealth() - $damage);

		return true;
	}
	public function heal(EntityRegainHealthEvent $source){
		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}

		$this->setHealth($this->getHealth() + $source->getAmount());
	}
	public function getHealth() : float{
		return $this->health;
	}
	public function isAlive(){
		return $this->health > 0;
	}
	public function canSaveWithChunk() : bool{
		return $this->savedWithChunk;
	}
	public function setCanSaveWithChunk(bool $value) : void{
		$this->savedWithChunk = $value;
	}
	public function setHealth(float $amount){
		if($amount == $this->health){
			return;
		}

		if($amount <= 0){
			if($this->isAlive()){
				$this->health = 0;
				$this->kill();
			}
		}elseif($amount <= $this->getMaxHealth() or $amount < $this->health){
			$this->health = $amount;
		}else{
			$this->health = $this->getMaxHealth();
		}
	}

	public function getAbsorption() : float{
		return 0;
	}

	public function setAbsorption(float $absorption){

	}
	public function setLastDamageCause(EntityDamageEvent $type){
		$this->lastDamageCause = $type;
	}
	public function getLastDamageCause(){
		return $this->lastDamageCause;
	}

	public function getAttributeMap() : AttributeMap{
		return $this->attributeMap;
	}
	public function getMaxHealth(){
		return $this->maxHealth;
	}
	public function setMaxHealth(int $amount){
		$this->maxHealth = $amount;
	}
	public function canCollideWith(Entity $entity){
		return !$this->justCreated and $entity !== $this;
	}

	public function canBeCollidedWith() : bool{
		return $this->isAlive();
	}
	protected function checkObstruction($x, $y, $z){
		if(count($this->level->getCollisionBoxes($this, $this->getBoundingBox(), false)) === 0){
			return false;
		}

		$i = (int) floor($x);
		$j = (int) floor($y);
		$k = (int) floor($z);

		$diffX = $x - $i;
		$diffY = $y - $j;
		$diffZ = $z - $k;

		if(BlockFactory::$solid[$this->level->getBlockIdAt($i, $j, $k)]){
			$flag = !BlockFactory::$solid[$this->level->getBlockIdAt($i - 1, $j, $k)];
			$flag1 = !BlockFactory::$solid[$this->level->getBlockIdAt($i + 1, $j, $k)];
			$flag2 = !BlockFactory::$solid[$this->level->getBlockIdAt($i, $j - 1, $k)];
			$flag3 = !BlockFactory::$solid[$this->level->getBlockIdAt($i, $j + 1, $k)];
			$flag4 = !BlockFactory::$solid[$this->level->getBlockIdAt($i, $j, $k - 1)];
			$flag5 = !BlockFactory::$solid[$this->level->getBlockIdAt($i, $j, $k + 1)];

			$direction = -1;
			$limit = 9999;

			if($flag){
				$limit = $diffX;
				$direction = 0;
			}

			if($flag1 and 1 - $diffX < $limit){
				$limit = 1 - $diffX;
				$direction = 1;
			}

			if($flag2 and $diffY < $limit){
				$limit = $diffY;
				$direction = 2;
			}

			if($flag3 and 1 - $diffY < $limit){
				$limit = 1 - $diffY;
				$direction = 3;
			}

			if($flag4 and $diffZ < $limit){
				$limit = $diffZ;
				$direction = 4;
			}

			if($flag5 and 1 - $diffZ < $limit){
				$direction = 5;
			}

			$force = lcg_value() * 0.2 + 0.1;

			if($direction === 0){
				$this->motion->x = -$force;

				return true;
			}

			if($direction === 1){
				$this->motion->x = $force;

				return true;
			}

			if($direction === 2){
				$this->motion->y = -$force;

				return true;
			}

			if($direction === 3){
				$this->motion->y = $force;

				return true;
			}

			if($direction === 4){
				$this->motion->z = -$force;

				return true;
			}

			if($direction === 5){
				$this->motion->z = $force;

				return true;
			}
		}

		return false;
	}
	public function entityBaseTick($tickDiff = 1){

		$this->justCreated = false;

		if(count($this->changedDataProperties) > 0){
			$this->sendData($this->hasSpawned, $this->changedDataProperties);
			$this->changedDataProperties = [];
		}

		if(count($this->effects) > 0){
			foreach($this->effects as $effect){
				if($effect->canTick()){
					$effect->applyEffect($this);
				}
				$effect->setDuration(max(0, $effect->getDuration() - $tickDiff));
				if($effect->getDuration() <= 0){
					$this->removeEffect($effect->getId());
				}
			}
		}

		$hasUpdate = false;

		$this->checkBlockCollision();

		if($this->y <= -16 and $this->isAlive()){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 10);
			$this->attack($ev);
			$hasUpdate = true;
		}

		if($this->fireTicks > 0){
			if($this->isFireProof()){
				if($this->fireTicks > 1){
					$this->fireTicks = 1;
				}else{
					$this->fireTicks -= 1;
				}
			}else{
				if(!$this->hasEffect(Effect::FIRE_RESISTANCE) and (($this->fireTicks % 20) === 0 or $tickDiff > 20)){
					$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FIRE_TICK, 1);
					$this->attack($ev);
				}
				$this->fireTicks -= $tickDiff;
			}

			if($this->fireTicks <= 0 && $this->fireTicks > -10){
				$this->extinguish();
			}else{
				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ONFIRE, true);
				$hasUpdate = true;
			}
		}

		if($this->noDamageTicks > 0){
			$this->noDamageTicks -= $tickDiff;
			if($this->noDamageTicks < 0){
				$this->noDamageTicks = 0;
			}
		}

		$this->ticksLived += $tickDiff;

		return $hasUpdate;
	}

	protected function updateMovement(bool $teleport = false){
		$diffPosition = ($this->x - $this->lastX) ** 2 + ($this->y - $this->lastY) ** 2 + ($this->z - $this->lastZ) ** 2;
		$diffRotation = ($this->yaw - $this->lastYaw) ** 2 + ($this->pitch - $this->lastPitch) ** 2;

		$diffMotion = $this->getMotion()->subtract($this->lastMotion)->lengthSquared();

		$still = $this->motion->lengthSquared() == 0.0;
		$wasStill = $this->lastMotion->lengthSquared() == 0.0;

		if($teleport or $diffPosition > 0.0001 or $diffRotation > 1.0 or (!$wasStill and $still)){
			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			$this->broadcastMovement($teleport);
		}

		if($diffMotion > 0.0025 or $wasStill !== $still){
			$this->lastMotion = clone $this->motion;

			$this->broadcastMotion();
		}
	}

	protected function applyDragBeforeGravity() : bool{
		return false;
	}

	protected function applyGravity(){
		$this->motion->y -= $this->gravity;
	}

	protected function tryChangeMovement(){
		$friction = 1 - $this->drag;

		if($this->applyDragBeforeGravity()){
			$this->motion->y *= $friction;
		}

		$this->applyGravity();

		if(!$this->applyDragBeforeGravity()){
			$this->motion->y *= $friction;
		}

		if($this->onGround){
			$friction *= $this->level->getBlockAt((int) floor($this->x), (int) floor($this->y - 1), (int) floor($this->z))->getFrictionFactor();
		}

		$this->motion->x *= $friction;
		$this->motion->z *= $friction;
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return new Vector3($vector3->x, $vector3->y + $this->baseOffset, $vector3->z);
	}

	protected function broadcastMovement(bool $teleport = false) : void{
		$pk = new MoveEntityPacket();
		$pk->entityRuntimeId = $this->id;
		$fix = $this->getOffsetPosition($this);
		$pk->x = $fix->x;
		$pk->y = $fix->y;
		$pk->z = $fix->z;
		$pk->yaw = $this->yaw;
		$pk->headYaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->teleported = $teleport;

		$this->level->broadcastPacketToViewers($this, $pk);
	}

	protected function broadcastMotion() : void{
		$pk = new SetEntityMotionPacket();
		$pk->entityRuntimeId = $this->id;
		$pk->motionX = $this->motion->x;
		$pk->motionY = $this->motion->y;
		$pk->motionZ = $this->motion->z;

		$this->level->broadcastPacketToViewers($this, $pk);
	}
	public function getDirectionVector(){
		$y = -sin(deg2rad($this->pitch));
		$xz = cos(deg2rad($this->pitch));
		$x = -$xz * sin(deg2rad($this->yaw));
		$z = $xz * cos(deg2rad($this->yaw));

		return $this->temporalVector->setComponents($x, $y, $z)->normalize();
	}
	public function getDirectionPlane(){
		return (new Vector2(-cos(deg2rad($this->yaw) - M_PI_2), -sin(deg2rad($this->yaw) - M_PI_2)))->normalize();
	}
	protected function onFirstUpdate(int $currentTick) : void{
		$this->server->getPluginManager()->callEvent(new EntitySpawnEvent($this));
	}
	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0){
			if(!$this->justCreated){
				$this->server->getLogger()->debug("Expected tick difference of at least 1, got $tickDiff for " . get_class($this));
			}

			return true;
		}

		$this->lastUpdate = $currentTick;

		if($this->justCreated){
			$this->onFirstUpdate($currentTick);
		}

		if(!$this->isAlive()){
			$this->deadTicks += $tickDiff;
			if($this->deadTicks >= $this->maxDeadTicks){
				$this->despawnFromAll();
				if(!$this->isPlayer){
					$this->flagForDespawn();
				}
			}

			return true;
		}

		$this->timings->startTiming();

		if($this->hasMovementUpdate()){
			$this->tryChangeMovement();

			if(abs($this->motion->x) <= self::MOTION_THRESHOLD){
				$this->motion->x = 0;
			}
			if(abs($this->motion->y) <= self::MOTION_THRESHOLD){
				$this->motion->y = 0;
			}
			if(abs($this->motion->z) <= self::MOTION_THRESHOLD){
				$this->motion->z = 0;
			}

			if($this->motion->x != 0 or $this->motion->y != 0 or $this->motion->z != 0){
				$this->move($this->motion->x, $this->motion->y, $this->motion->z);
			}

			$this->forceMovementUpdate = false;
		}

		$this->updateMovement();

		Timings::$timerEntityBaseTick->startTiming();
		$hasUpdate = $this->entityBaseTick($tickDiff);
		Timings::$timerEntityBaseTick->stopTiming();

		$this->timings->stopTiming();

		return ($hasUpdate or $this->hasMovementUpdate());
	}

	public function onNearbyBlockChange() : void{
		$this->setForceMovementUpdate();

		$this->scheduleUpdate();
	}
	final public function setForceMovementUpdate(bool $value = true) : void{
		$this->forceMovementUpdate = $value;

		$this->blocksAround = null;
	}
	public function hasMovementUpdate() : bool{
		return (
			$this->forceMovementUpdate or
			$this->motion->x != 0 or
			$this->motion->y != 0 or
			$this->motion->z != 0 or
			!$this->onGround
		);
	}

	public final function scheduleUpdate(){
		if($this->closed){
			$this->server->getLogger()->warning("Cannot schedule update on garbage entity " . get_class($this));
			return;
		}
		$this->level->updateEntities[$this->id] = $this;
	}
	public function isOnFire(){
		return $this->fireTicks > 0;
	}
	public function setOnFire($seconds){
		$ticks = $seconds * 20;
		if($ticks > $this->fireTicks){
			$this->fireTicks = $ticks;
		}
	}
	public function getFireTicks() : int{
		return $this->fireTicks;
	}
	public function setFireTicks(int $fireTicks) : void{
		$this->fireTicks = $fireTicks;
	}
	public function isFireProof() : bool{
		return false;
	}
	public function getDirection(){
		$rotation = fmod($this->yaw - 90, 360);
		if($rotation < 0){
			$rotation += 360.0;
		}
		if((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)){
			return 2;
		}elseif(45 <= $rotation and $rotation < 135){
			return 3;
		}elseif(135 <= $rotation and $rotation < 225){
			return 0;
		}elseif(225 <= $rotation and $rotation < 315){
			return 1;
		}else{
			return null;
		}
	}

	public function extinguish(){
		$this->fireTicks = 0;
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ONFIRE, false);
	}
	public function canTriggerWalking(){
		return true;
	}

	public function resetFallDistance(){
		$this->fallDistance = 0.0;
	}
	protected function updateFallState($distanceThisTick, $onGround){
		if($distanceThisTick < $this->fallDistance){
			$this->fallDistance -= $distanceThisTick;
		}else{
			$this->fallDistance = 0;
		}
		if($onGround && $this->fallDistance > 0){
			if($this instanceof Living){
				$this->fall($this->fallDistance);
			}
			$this->resetFallDistance();
		}
	}
	public function getBoundingBox(){
		return $this->boundingBox;
	}

	protected function recalculateBoundingBox() : void{
		$halfWidth = $this->width / 2;

		$this->boundingBox = new AxisAlignedBB(
			$this->x - $halfWidth,
			$this->y + $this->ySize,
			$this->z - $halfWidth,
			$this->x + $halfWidth,
			$this->y + $this->height + $this->ySize,
			$this->z + $halfWidth
		);
	}
	public function fall($fallDistance){
		if($this instanceof Player and $this->isSpectator()){
			return;
		}
		if($fallDistance > 3){
			$this->getLevel()->addParticle(new DestroyBlockParticle($this, $this->getLevel()->getBlock($this->floor()->subtract(0, 1, 0))));
		}
		if($this->isInsideOfWater()){
			return;
		}
		$damage = ceil($fallDistance - 3 - ($this->hasEffect(Effect::JUMP) ? $this->getEffect(Effect::JUMP)->getEffectLevel() : 0));

		if($this->getLevel()->getBlock($this->floor()->subtract(0, 1, 0)) instanceof SlimeBlock){
			$damage = 0;
		}
		if($this instanceof Player){
			if($this->getInventory()->getChestplate() instanceof Elytra){
				$damage = 0;
			}
		}
		if($damage > 0){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev);
		}
	}
	public function getEyeHeight(){
		return $this->eyeHeight;
	}
	public function onCollideWithPlayer(Player $player){

	}
	protected function switchLevel(Level $targetLevel){
		if($this->closed){
			return false;
		}

		if($this->isValid()){
			$this->server->getPluginManager()->callEvent($ev = new EntityLevelChangeEvent($this, $this->level, $targetLevel));
			if($ev->isCancelled()){
				return false;
			}

			$this->level->removeEntity($this);
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->despawnFromAll();
		}

		$this->setLevel($targetLevel);
		$this->level->addEntity($this);
		$this->chunk = null;

		return true;
	}
	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
	}
	public function getLocation(){
		return new Location($this->x, $this->y, $this->z, $this->yaw, $this->pitch, $this->level);
	}
	public function isInsideOfPortal(){
		$blocks = $this->getBlocksAround();

		foreach($blocks as $block){
			if($block instanceof Portal){
				return true;
			}
		}

		return false;
	}
	public function isInsideOfWater(){
		if($this->level == null) return false;
		$block = $this->level->getBlockAt(Math::floorFloat($this->x), Math::floorFloat($y = ($this->y + $this->getEyeHeight())), Math::floorFloat($this->z));

		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}

		return false;
	}

	public function isUnderwater() : bool{
		$block = $this->level->getBlockAt((int) floor($this->x), (int) floor($y = ($this->y + $this->getEyeHeight())), (int) floor($this->z));

		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}

		return false;
	}
	public function isInsideOfSolid(){
		$block = $this->level->getBlockAt((int) floor($this->x), (int) floor(($this->y + $this->getEyeHeight())), (int) floor($this->z));

		return $block->isSolid() and !$block->isTransparent() and $block->collidesWithBB($this->getBoundingBox());
	}
	public function isInsideOfFire(){
		foreach($this->getBlocksAround() as $block){
			if($block instanceof Fire){
				return true;
			}
		}

		return false;
	}
	public function fastMove($dx, $dy, $dz){
		$this->blocksAround = null;

		if($dx == 0 and $dz == 0 and $dy == 0){
			return true;
		}

		Timings::$entityMoveTimer->startTiming();

		$newBB = $this->boundingBox->offsetCopy($dx, $dy, $dz);

		$list = $this->level->getCollisionBoxes($this, $newBB, false);

		if(count($list) === 0){
			$this->boundingBox = $newBB;
		}

		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		$this->checkChunks();

		if(!$this->onGround or $dy != 0){
			$bb = clone $this->boundingBox;
			$bb->minY -= 0.75;
			$this->onGround = false;

			if(count($this->level->getCollisionBlocks($bb)) > 0){
				$this->onGround = true;
			}
		}
		$this->isCollided = $this->onGround;
		$this->updateFallState($dy, $this->onGround);

		Timings::$entityMoveTimer->stopTiming();

		return true;
	}

	public function move($dx, $dy, $dz) : void{
		$this->blocksAround = null;

		Timings::$entityMoveTimer->startTiming();

		$movX = $dx;
		$movY = $dy;
		$movZ = $dz;

		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
		}else{
			$this->ySize *= self::STEP_CLIP_MULTIPLIER;

			/*
			if($this->isColliding){
				$this->isColliding = false;
				$dx *= 0.25;
				$dy *= 0.05;
				$dz *= 0.25;
				$this->motion->x = 0;
				$this->motion->y = 0;
				$this->motion->z = 0;
			}
			*/

			$moveBB = clone $this->boundingBox;

			/*$sneakFlag = $this->onGround and $this instanceof Player;

			if($sneakFlag){
				for($mov = 0.05; $dx != 0.0 and count($this->level->getCollisionBoxes($this, $this->boundingBox->offsetCopy($dx, -1, 0))) === 0; $movX = $dx){
					if($dx < $mov and $dx >= -$mov){
						$dx = 0;
					}elseif($dx > 0){
						$dx -= $mov;
					}else{
						$dx += $mov;
					}
				}

				for(; $dz != 0.0 and count($this->level->getCollisionBoxes($this, $this->boundingBox->offsetCopy(0, -1, $dz))) === 0; $movZ = $dz){
					if($dz < $mov and $dz >= -$mov){
						$dz = 0;
					}elseif($dz > 0){
						$dz -= $mov;
					}else{
						$dz += $mov;
					}
				}

			}*/

			assert(abs($dx) <= 20 and abs($dy) <= 20 and abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");

			$list = $this->level->getCollisionBoxes($this, $this->level->getTickRateTime() > 50 ? $moveBB->offsetCopy($dx, $dy, $dz) : $moveBB->addCoord($dx, $dy, $dz), false);

			foreach($list as $bb){
				$dy = $bb->calculateYOffset($moveBB, $dy);
			}

			$moveBB->offset(0, $dy, 0);

			$fallingFlag = ($this->onGround or ($dy != $movY and $movY < 0));

			foreach($list as $bb){
				$dx = $bb->calculateXOffset($moveBB, $dx);
			}

			$moveBB->offset($dx, 0, 0);

			foreach($list as $bb){
				$dz = $bb->calculateZOffset($moveBB, $dz);
			}

			$moveBB->offset(0, 0, $dz);

			if($this->stepHeight > 0 and $fallingFlag and ($movX != $dx or $movZ != $dz)){
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $movX;
				$dy = $this->stepHeight;
				$dz = $movZ;

				$stepBB = clone $this->boundingBox;

				$list = $this->level->getCollisionBoxes($this, $stepBB->addCoord($dx, $dy, $dz), false);

				foreach($list as $bb){
					$dy = $bb->calculateYOffset($stepBB, $dy);
				}

				$stepBB->offset(0, $dy, 0);

				foreach($list as $bb){
					$dx = $bb->calculateXOffset($stepBB, $dx);
				}

				$stepBB->offset($dx, 0, 0);

				foreach($list as $bb){
					$dz = $bb->calculateZOffset($stepBB, $dz);
				}

				$stepBB->offset(0, 0, $dz);

				$reverseDY = -$dy;
				foreach($list as $bb){
					$reverseDY = $bb->calculateYOffset($stepBB, $reverseDY);
				}
				$dy += $reverseDY;
				$stepBB->offset(0, $reverseDY, 0);

				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)){
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
				}else{
					$moveBB = $stepBB;
					$this->ySize += $dy;
				}
			}
			$this->boundingBox = $moveBB;
		}

		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		$this->checkChunks();
		$this->checkBlockCollision();
		$this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
		$this->updateFallState($dy, $this->onGround);

		if($movX != $dx){
			$this->motion->x = 0;
		}

		if($movY != $dy){
			$this->motion->y = 0;
		}

		if($movZ != $dz){
			$this->motion->z = 0;
		}


		Timings::$entityMoveTimer->stopTiming();
	}
	protected function checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz){
		$this->isCollidedVertically = $movY != $dy;
		$this->isCollidedHorizontally = ($movX != $dx or $movZ != $dz);
		$this->isCollided = ($this->isCollidedHorizontally or $this->isCollidedVertically);
		$this->onGround = ($movY != $dy and $movY < 0);
	}
	public function getBlocksAround(){
		if($this->blocksAround === null){
			$inset = 0.001;

			$minX = (int) floor($this->boundingBox->minX + $inset);
			$minY = (int) floor($this->boundingBox->minY + $inset);
			$minZ = (int) floor($this->boundingBox->minZ + $inset);
			$maxX = (int) floor($this->boundingBox->maxX - $inset);
			$maxY = (int) floor($this->boundingBox->maxY - $inset);
			$maxZ = (int) floor($this->boundingBox->maxZ - $inset);

			$this->blocksAround = [];

			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->level->getBlockAt($x, $y, $z);
						if($block->hasEntityCollision()){
							$this->blocksAround[] = $block;
						}
					}
				}
			}
		}

		return $this->blocksAround;
	}
	public function canBeMovedByCurrents() : bool{
		return true;
	}

	protected function checkBlockCollision(){
		$vectors = [];

		foreach($this->getBlocksAround() as $block){
			if(!$block->onEntityInside($this)){
				$this->blocksAround = null;
			}
			if(($v = $block->addVelocityToEntity($this)) !== null){
				$vectors[] = $v;
			}
		}

		$vector = Vector3::sum(...$vectors);
		if($vector->lengthSquared() > 0){
			$vector = $vector->normalize();
			$d = 0.014;
			$this->motion->x += $vector->x * $d;
			$this->motion->y += $vector->y * $d;
			$this->motion->z += $vector->z * $d;
		}
	}

	public function setRotation(float $yaw, float $pitch) : void{
		$this->yaw = $yaw;
		$this->pitch = $pitch;

		$this->scheduleUpdate();
	}

	public function setPositionAndRotation(Vector3 $pos, float $yaw, float $pitch) : bool{
		if($this->setPosition($pos)){
			$this->setRotation($yaw, $pitch);

			return true;
		}

		return false;
	}

	protected function checkChunks(){
		$chunkX = $this->getFloorX() >> 4;
		$chunkZ = $this->getFloorZ() >> 4;
		if($this->chunk === null or ($this->chunk->getX() !== $chunkX or $this->chunk->getZ() !== $chunkZ)){
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->chunk = $this->level->getChunk($chunkX, $chunkZ, true);

			if(!$this->justCreated){
				$newChunk = $this->level->getViewersForPosition($this);
				foreach($this->hasSpawned as $player){
					if(!isset($newChunk[$player->getLoaderId()])){
						$this->despawnFrom($player);
					}else{
						unset($newChunk[$player->getLoaderId()]);
					}
				}
				foreach($newChunk as $player){
					$this->spawnTo($player);
				}
			}

			if($this->chunk === null){
				return;
			}

			$this->chunk->addEntity($this);
		}
	}
	public function setLocation(Location $pos){
		if($this->closed){
			return false;
		}

		$this->setPositionAndRotation($pos, $pos->yaw, $pos->pitch);

		return true;
	}
	public function setPosition(Vector3 $pos){
		if($this->closed){
			return false;
		}

		if($pos instanceof Position and $pos->level !== null and $pos->level !== $this->level){
			if($this->switchLevel($pos->getLevel()) === false){
				return false;
			}
		}

		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;

		$this->recalculateBoundingBox();

		$this->blocksAround = null;

		$this->checkChunks();

		return true;
	}
	public function setPositionTo(int $x, int $y, int $z) : bool{
		if($this->closed){
			return false;
		}

		$this->x = $x;
		$this->y = $y;
		$this->z = $z;

		$this->recalculateBoundingBox();

		$this->blocksAround = null;

		$this->checkChunks();

		return true;
	}

	protected function resetLastMovements() : void{
		list($this->lastX, $this->lastY, $this->lastZ) = [$this->x, $this->y, $this->z];
		list($this->lastYaw, $this->lastPitch) = [$this->yaw, $this->pitch];
		$this->lastMotion = clone $this->motion;
	}

	public function getMotion() : Vector3{
		return clone $this->motion;
	}
	public function setMotion(Vector3 $motion){
		if(!$this->justCreated){
			$this->server->getPluginManager()->callEvent($ev = new EntityMotionEvent($this, $motion));
			if($ev->isCancelled()){
				return false;
			}
		}

		$this->motion->x = $motion->x;
		$this->motion->y = $motion->y;
		$this->motion->z = $motion->z;

		if(!$this->justCreated){
			$this->updateMovement();
		}

		return true;
	}
	public function isOnGround(){
		return $this->onGround === true;
	}

	public function kill(){
		$this->health = 0;
		$this->removeAllEffects();

		$this->scheduleUpdate();

		if($this->getLevel()->getServer()->expEnabled){
			$exp = mt_rand($this->getDropExpMin(), $this->getDropExpMax());
			if($exp > 0) $this->getLevel()->spawnXPOrb($this, $exp);
		}
	}
	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if($pos instanceof Location){
			$yaw = $yaw ?? $pos->yaw;
			$pitch = $pitch ?? $pos->pitch;
		}
		$from = Position::fromObject($this, $this->level);
		$to = Position::fromObject($pos, $pos instanceof Position ? $pos->getLevel() : $this->level);
		$this->server->getPluginManager()->callEvent($ev = new EntityTeleportEvent($this, $from, $to));
		if($ev->isCancelled()){
			return false;
		}
		$this->ySize = 0;
		$pos = $ev->getTo();

		$this->setMotion($this->temporalVector->setComponents(0, 0, 0));
		if($this->setPositionAndRotation($pos, $yaw ?? $this->yaw, $pitch ?? $this->pitch)){
			$this->resetFallDistance();
			$this->setForceMovementUpdate();

			$this->updateMovement(true);

			return true;
		}

		return false;
	}
	public function getId() : int{
		return $this->id;
	}

	public function respawnToAll(){
		foreach($this->hasSpawned as $key => $player){
			unset($this->hasSpawned[$key]);
			$this->spawnTo($player);
		}
	}

	public function spawnToAll(){
		if($this->chunk === null or $this->closed){
			return;
		}
		foreach($this->level->getViewersForPosition($this) as $player){
			if($player->isOnline()){
				$this->spawnTo($player);
			}
		}
	}
	public function despawnFromAll(){
		foreach($this->hasSpawned as $player){
			$this->despawnFrom($player);
		}
	}
	public function flagForDespawn() : void{
		$this->needsDespawn = true;
		$this->scheduleUpdate();
	}

	public function isFlaggedForDespawn() : bool{
		return $this->needsDespawn;
	}
	public function isClosed() : bool{
		return $this->closed;
	}
	public function close(){
		if($this->closeInFlight){
			return;
		}

		if(!$this->closed){
			$this->closeInFlight = true;
			$this->server->getPluginManager()->callEvent(new EntityDespawnEvent($this));
			$this->closed = true;

			$this->removeEffect(Effect::HEALTH_BOOST);

			$this->despawnFromAll();
			$this->hasSpawned = [];

			if($this->linkedType != 0){
				$this->linkedEntity->setLinked(0, $this);
			}

			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
				$this->chunk = null;
			}

			if($this->isValid()){
				$this->level->removeEntity($this);
				$this->setLevel(null);
			}

			$this->namedtag = null;
			$this->lastDamageCause = null;
			$this->closeInFlight = false;
		}
	}
	public function setDataProperty($id, $type, $value, bool $send = true){
		if($this->getDataProperty($id) !== $value){
			$this->dataProperties[$id] = [$type, $value];
			if($send){
				$this->changedDataProperties[$id] = $this->dataProperties[$id];
			}

			return true;
		}

		return false;
	}
	public function linkEntity(Entity $entity){
		return $this->setLinked(1, $entity);
	}

	public function sendLinkedData(){
		if($this->linkedEntity instanceof Entity){
			$this->setLinked($this->linkedType, $this->linkedEntity);
		}
	}
	public function setLinked($type, Entity $entity){
		if($entity instanceof Boat or $entity instanceof Minecart){
			$this->setDataProperty(self::DATA_RIDER_SEAT_POSITION, 8, [0, 1, 0]);
		}

		if($type != 0 and $entity === null){
			return false;
		}
		if($entity === $this){
			return false;
		}
		switch($type){
			case 0:
				if($this->linkedType == 0){
					return true;
				}
				$this->linkedType = 0;
				$pk = new SetEntityLinkPacket();
				$pk->from = $entity->getId();
				$pk->to = $this->getId();
				$pk->type = 3;
				$this->server->broadcastPacket($this->level->getPlayers(), $pk);
				if($this instanceof Player){
					$pk = new SetEntityLinkPacket();
					$pk->from = $entity->getId();
					$pk->to = 0;
					$pk->type = 3;
					$this->dataPacket($pk);
				}
				if($this->linkedEntity->getLinkedType()){
					$this->linkedEntity->setLinked(0, $this);
				}
				$this->linkedEntity = null;

				return true;
			case 1:
				if(!$entity->isAlive()){
					return false;
				}
				$this->linkedEntity = $entity;
				$this->linkedType = 1;
				$entity->linkedEntity = $this;
				$entity->linkedType = 1;
				$pk = new SetEntityLinkPacket();
				$pk->from = $entity->getId();
				$pk->to = $this->getId();
				$pk->type = 2;
				$this->server->broadcastPacket($this->level->getPlayers(), $pk);
				if($this instanceof Player){
					$pk = new SetEntityLinkPacket();
					$pk->from = $entity->getId();
					$pk->to = 0;
					$pk->type = 2;
					$this->dataPacket($pk);
				}

				return true;
			case 2:
				if(!$entity->isAlive()){
					return false;
				}
				if($entity->getLinkedEntity() !== $this){
					return $entity->linkEntity($this);
				}
				$this->linkedEntity = $entity;
				$this->linkedType = 2;

				return true;
			default:
				return false;
		}
	}
	public function getLinkedEntity(){
		return $this->linkedEntity;
	}
	public function getLinkedType(){
		return $this->linkedType;
	}
	public function getDataProperty($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][1] : null;
	}
	public function getDataPropertyType($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][0] : null;
	}
	public function setDataFlag($propertyId, $id, $value = true, $type = self::DATA_TYPE_LONG){
		if($this->getDataFlag($propertyId, $id) !== $value){
			$flags = (int) $this->getDataProperty($propertyId);
			$flags ^= 1 << $id;
			$this->setDataProperty($propertyId, $type, $flags);
		}
	}
	public function getDataFlag($propertyId, $id){
		return (((int) $this->getDataProperty($propertyId)) & (1 << $id)) > 0;
	}
	public function getGenericFlag(int $flagId) : bool{
		return $this->getDataFlag($flagId >= 64 ? self::DATA_FLAGS2 : self::DATA_FLAGS, $flagId % 64);
	}
	public function setGenericFlag(int $flagId, bool $value = true) : void{
		$this->setDataFlag($flagId >= 64 ? self::DATA_FLAGS2 : self::DATA_FLAGS, $flagId % 64, $value, self::DATA_TYPE_LONG);
	}

	public function __destruct(){
		$this->close();
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
		$this->server->getEntityMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getEntityMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getEntityMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
		$this->server->getEntityMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
	}
	public function __toString(){
		return (new ReflectionClass($this))->getShortName() . "(" . $this->getId() . ")";
	}
}