<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\item;

use InvalidArgumentException;
use JsonSerializable;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\entity\CaveSpider;
use pocketmine\entity\Entity;
use pocketmine\entity\PigZombie;
use pocketmine\entity\Silverfish;
use pocketmine\entity\Skeleton;
use pocketmine\entity\Spider;
use pocketmine\entity\Witch;
use pocketmine\entity\Zombie;
use pocketmine\inventory\Fuel;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;
use RuntimeException;
use SplFixedArray;

class Item implements ItemIds, JsonSerializable{
	private static $cachedParser = null;
	protected static function parseCompoundTag(string $tag) : CompoundTag{
		if($tag === ""){
			throw new \InvalidArgumentException("No NBT data found in supplied string");
		}

		if(self::$cachedParser === null){
			self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		self::$cachedParser->read($tag);
		$data = self::$cachedParser->getData();
		if(!($data instanceof CompoundTag)){
			throw new \InvalidArgumentException("Invalid item NBT string given, it could not be deserialized");
		}

		return $data;
	}
	protected static function writeCompoundTag(CompoundTag $tag) : string{
		if(self::$cachedParser === null){
			self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		self::$cachedParser->setData($tag);
		return self::$cachedParser->write();
	}
	public static $list = null;
	protected $block;
	protected $id;
	protected $meta;
	private $nbt = null;
	public $count;
	protected $name;
	public static function init($readFromJson = false, bool $registerCreativeItems = true){
		if(self::$list === null){
			self::$list = new SplFixedArray(65536);
			self::$list[self::ACACIA_DOOR] = AcaciaDoor::class;
			self::$list[self::APPLE] = Apple::class;
			self::$list[self::ARROW] = Arrow::class;

			self::$list[self::BAKED_POTATO] = BakedPotato::class;
			self::$list[self::BED] = Bed::class;
			self::$list[self::BEETROOT] = Beetroot::class;
			self::$list[self::BEETROOT_SEEDS] = BeetrootSeeds::class;
			self::$list[self::BEETROOT_SOUP] = BeetrootSoup::class;
			self::$list[self::BIRCH_DOOR] = BirchDoor::class;
			self::$list[self::BLAZE_POWDER] = BlazePowder::class;
			self::$list[self::BOAT] = Boat::class;
			self::$list[self::BONE] = Bone::class;
			self::$list[self::BOOK] = Book::class;
			self::$list[self::BOW] = Bow::class;
			self::$list[self::BOWL] = Bowl::class;
			self::$list[self::BREAD] = Bread::class;
			self::$list[self::BREWING_STAND] = BrewingStand::class;
			self::$list[self::BRICK] = Brick::class;
			self::$list[self::BUCKET] = Bucket::class;

			self::$list[self::CAKE] = Cake::class;
			self::$list[self::CAMERA] = Camera::class;
			self::$list[self::CARROT] = Carrot::class;
			self::$list[self::CAULDRON] = Cauldron::class;
			self::$list[self::CHAIN_BOOTS] = ChainBoots::class;
			self::$list[self::CHAIN_CHESTPLATE] = ChainChestplate::class;
			self::$list[self::CHAIN_HELMET] = ChainHelmet::class;
			self::$list[self::CHAIN_LEGGINGS] = ChainLeggings::class;
			self::$list[self::CHORUS_FRUIT] = ChorusFruit::class;
			self::$list[self::CLAY] = Clay::class;
			self::$list[self::CLOCK] = Clock::class;
			self::$list[self::COAL] = Coal::class;
			self::$list[self::COMPASS] = Compass::class;
			self::$list[self::COOKED_CHICKEN] = CookedChicken::class;
			self::$list[self::COOKED_FISH] = CookedFish::class;
			self::$list[self::COOKED_MUTTON] = CookedMutton::class;
			self::$list[self::COOKED_PORKCHOP] = CookedPorkchop::class;
			self::$list[self::COOKED_RABBIT] = CookedRabbit::class;
			self::$list[self::COOKIE] = Cookie::class;

			self::$list[self::DARK_OAK_DOOR] = DarkOakDoor::class;
			self::$list[self::DIAMOND] = Diamond::class;
			self::$list[self::DIAMOND_AXE] = DiamondAxe::class;
			self::$list[self::DIAMOND_BOOTS] = DiamondBoots::class;
			self::$list[self::DIAMOND_CHESTPLATE] = DiamondChestplate::class;
			self::$list[self::DIAMOND_HELMET] = DiamondHelmet::class;
			self::$list[self::DIAMOND_HOE] = DiamondHoe::class;
			self::$list[self::DIAMOND_LEGGINGS] = DiamondLeggings::class;
			self::$list[self::DIAMOND_PICKAXE] = DiamondPickaxe::class;
			self::$list[self::DIAMOND_SHOVEL] = DiamondShovel::class;
			self::$list[self::DIAMOND_SWORD] = DiamondSword::class;
			self::$list[self::DRAGONS_BREATH] = DragonsBreath::class;
			self::$list[self::DYE] = Dye::class;

			self::$list[self::EGG] = Egg::class;
			self::$list[self::ELYTRA] = Elytra::class;
			self::$list[self::EMERALD] = Emerald::class;
			self::$list[self::ENCHANTED_BOOK] = EnchantedBook::class;
			self::$list[self::ENCHANTED_GOLDEN_APPLE] = EnchantedGoldenApple::class;
			self::$list[self::ENCHANTING_BOTTLE] = EnchantingBottle::class;
			self::$list[self::ENDER_PEARL] = EnderPearl::class;
			self::$list[self::END_CRYSTAL] = EnderCrystal::class;
			self::$list[self::EYE_OF_ENDER] = EyeOfEnder::class;

			self::$list[self::FEATHER] = Feather::class;
			self::$list[self::FERMENTED_SPIDER_EYE] = FermentedSpiderEye::class;
			self::$list[self::FIRE_CHARGE] = FireCharge::class;
			self::$list[self::FISHING_ROD] = FishingRod::class;
			self::$list[self::FLINT] = Flint::class;
			self::$list[self::FLINT] = Flint::class;
			self::$list[self::FLINT_STEEL] = FlintSteel::class;
			self::$list[self::FLOWER_POT] = FlowerPot::class;

			self::$list[self::GLASS_BOTTLE] = GlassBottle::class;
			self::$list[self::GLISTERING_MELON] = GlisteringMelon::class;
			self::$list[self::GLOWSTONE_DUST] = GlowstoneDust::class;
			self::$list[self::GOLDEN_APPLE] = GoldenApple::class;
			self::$list[self::GOLDEN_CARROT] = GoldenCarrot::class;
			self::$list[self::GOLD_AXE] = GoldAxe::class;
			self::$list[self::GOLD_BOOTS] = GoldBoots::class;
			self::$list[self::GOLD_CHESTPLATE] = GoldChestplate::class;
			self::$list[self::GOLD_HELMET] = GoldHelmet::class;
			self::$list[self::GOLD_HOE] = GoldHoe::class;
			self::$list[self::GOLD_INGOT] = GoldIngot::class;
			self::$list[self::GOLD_LEGGINGS] = GoldLeggings::class;
			self::$list[self::GOLD_NUGGET] = GoldNugget::class;
			self::$list[self::GOLD_PICKAXE] = GoldPickaxe::class;
			self::$list[self::GOLD_SHOVEL] = GoldShovel::class;
			self::$list[self::GOLD_SWORD] = GoldSword::class;
			self::$list[self::GUNPOWDER] = Gunpowder::class;

			self::$list[self::HOPPER] = Hopper::class;

			self::$list[self::IRON_AXE] = IronAxe::class;
			self::$list[self::IRON_BOOTS] = IronBoots::class;
			self::$list[self::IRON_CHESTPLATE] = IronChestplate::class;
			self::$list[self::IRON_DOOR] = IronDoor::class;
			self::$list[self::IRON_HELMET] = IronHelmet::class;
			self::$list[self::IRON_HOE] = IronHoe::class;
			self::$list[self::IRON_INGOT] = IronIngot::class;
			self::$list[self::IRON_LEGGINGS] = IronLeggings::class;
			self::$list[self::IRON_PICKAXE] = IronPickaxe::class;
			self::$list[self::IRON_SHOVEL] = IronShovel::class;
			self::$list[self::IRON_SWORD] = IronSword::class;
			self::$list[self::ITEM_FRAME] = ItemFrame::class;

			self::$list[self::JUNGLE_DOOR] = JungleDoor::class;

			self::$list[self::LEATHER] = Leather::class;
			self::$list[self::LEATHER_BOOTS] = LeatherBoots::class;
			self::$list[self::LEATHER_CAP] = LeatherCap::class;
			self::$list[self::LEATHER_PANTS] = LeatherPants::class;
			self::$list[self::LEATHER_TUNIC] = LeatherTunic::class;

			self::$list[self::MAGMA_CREAM] = MagmaCream::class;
			self::$list[self::MELON] = Melon::class;
			self::$list[self::MELON_SEEDS] = MelonSeeds::class;
			self::$list[self::MINECART] = Minecart::class;
			self::$list[self::MINECART_WITH_TNT] = MinecartTNT::class;
			self::$list[self::MUSHROOM_STEW] = MushroomStew::class;

			self::$list[self::NETHER_BRICK] = NetherBrick::class;
			self::$list[self::NETHER_QUARTZ] = NetherQuartz::class;
			self::$list[self::NETHER_STAR] = NetherStar::class;
			self::$list[self::NETHER_WART] = NetherWart::class;

			self::$list[self::PAINTING] = Painting::class;
			self::$list[self::PAPER] = Paper::class;
			self::$list[self::POPPED_CHORUS_FRUIT] = PoppedChorusFruit::class;
			self::$list[self::POTATO] = Potato::class;
			self::$list[self::POTION] = Potion::class;
			self::$list[self::PRISMARINE_CRYSTALS] = PrismarineCrystals::class;
			self::$list[self::PRISMARINE_SHARD] = PrismarineShard::class;
			self::$list[self::PUMPKIN_PIE] = PumpkinPie::class;
			self::$list[self::PUMPKIN_SEEDS] = PumpkinSeeds::class;

			self::$list[self::QUARTZ] = Quartz::class;

			self::$list[self::RABBIT_STEW] = RabbitStew::class;
			self::$list[self::RAW_BEEF] = RawBeef::class;
			self::$list[self::RAW_CHICKEN] = RawChicken::class;
			self::$list[self::RAW_FISH] = Fish::class;
			self::$list[self::RAW_MUTTON] = RawMutton::class;
			self::$list[self::RAW_PORKCHOP] = RawPorkchop::class;
			self::$list[self::RAW_RABBIT] = RawRabbit::class;
			self::$list[self::REDSTONE] = Redstone::class;
			self::$list[self::REPEATER] = Repeater::class;
			self::$list[self::ROTTEN_FLESH] = RottenFlesh::class;

			self::$list[self::SADDLE] = Saddle::class;
			self::$list[self::SHEARS] = Shears::class;
			self::$list[self::SHULKER_SHELL] = ShulkerShell::class;
			self::$list[self::SIGN] = Sign::class;
			self::$list[self::SKULL] = Skull::class;
			self::$list[self::SLIMEBALL] = Slimeball::class;
			self::$list[self::SNOWBALL] = Snowball::class;
			self::$list[self::SPAWN_EGG] = SpawnEgg::class;
			self::$list[self::SPIDER_EYE] = SpiderEye::class;
			self::$list[self::SPLASH_POTION] = SplashPotion::class;
			self::$list[self::SPRUCE_DOOR] = SpruceDoor::class;
			self::$list[self::STEAK] = Steak::class;
			self::$list[self::STICK] = Stick::class;
			self::$list[self::STONE_AXE] = StoneAxe::class;
			self::$list[self::STONE_HOE] = StoneHoe::class;
			self::$list[self::STONE_PICKAXE] = StonePickaxe::class;
			self::$list[self::STONE_SHOVEL] = StoneShovel::class;
			self::$list[self::STONE_SWORD] = StoneSword::class;
			self::$list[self::STRING] = ItemString::class;
			self::$list[self::SUGAR] = Sugar::class;
			self::$list[self::SUGARCANE] = Sugarcane::class;

			self::$list[self::TOTEM] = Totem::class;

			self::$list[self::WHEAT] = Wheat::class;
			self::$list[self::WHEAT_SEEDS] = WheatSeeds::class;
			self::$list[self::WOODEN_AXE] = WoodenAxe::class;
			self::$list[self::WOODEN_DOOR] = WoodenDoor::class;
			self::$list[self::WOODEN_HOE] = WoodenHoe::class;
			self::$list[self::WOODEN_PICKAXE] = WoodenPickaxe::class;
			self::$list[self::WOODEN_SHOVEL] = WoodenShovel::class;
			self::$list[self::WOODEN_SWORD] = WoodenSword::class;

			for($i = 0; $i < 256; ++$i){
				if(BlockFactory::$list[$i] !== null){
					self::$list[$i] = BlockFactory::$list[$i];
				}
			}
		}

		if($registerCreativeItems)
			self::initCreativeItems();
	}
	public static function registerItem(Item $item, bool $override = false){
		$id = $item->getId();
		if(!$override and self::isRegistered($id)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		self::$list[self::getListOffset($id)] = clone $item;
	}

	private static function initCreativeItems(){
		self::clearCreativeItems();

		$creativeItems = new Config(\pocketmine\PATH . "src/pocketmine/resources/creativeitems.json", Config::JSON, []);

		foreach($creativeItems->getAll() as $data){
			$item = Item::jsonDeserialize($data);
			if($item->getName() === "Unknown"){
				continue;
			}

			self::addCreativeItem($item);
		}
	}

	public static function clearCreativeItems(){
		CreativeItemsStorage::getInstance()->clearItems();
	}
	public static function getCreativeItems() : array{
		return CreativeItemsStorage::getInstance()->getItems();
	}
	public static function addCreativeItem(Item $item){
		CreativeItemsStorage::getInstance()->addItem($item);
	}
	public static function removeCreativeItem(Item $item){
		$index = self::getCreativeItemIndex($item);
		if($index !== -1){
			CreativeItemsStorage::getInstance()->removeItemByIndex($index);
		}
	}
	public static function isCreativeItem(Item $item) : bool{
		foreach(CreativeItemsStorage::getInstance()->getItems() as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return true;
			}
		}

		return false;
	}
	public static function getCreativeItem(int $index){
		return CreativeItemsStorage::getInstance()->getItemByIndex($index);
	}
	public static function getCreativeItemIndex(Item $item) : int{
		foreach(self::getCreativeItems() as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return $i;
			}
		}

		return -1;
	}
	public static function get(int $id, int $meta = 0, int $count = 1, string $tags = "") : Item{
		try{
			$class = self::$list[$id];
			if($class === null){
				return (new Item($id, $meta, $count))->setCompoundTag($tags);
			}elseif($id < 256){
				return (new ItemBlock(new $class($meta), $meta, $count))->setCompoundTag($tags);
			}else{
				return (new $class($meta, $count))->setCompoundTag($tags);
			}
		}catch(RuntimeException $e){
			return (new Item($id, $meta, $count))->setCompoundTag($tags);
		}
	}
	public static function fromString(string $str, bool $multiple = false){
		if($multiple){
			$blocks = [];
			foreach(explode(",", $str) as $b){
				$blocks[] = self::fromString($b, false);
			}

			return $blocks;
		}else{
			$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}elseif(is_numeric($b[1])){
				$meta = (int) $b[1];
			}else{
				throw new InvalidArgumentException("Unable to parse \"" . $b[1] . "\" from \"" . $str . "\" as a valid meta value");
			}

			if(is_numeric($b[0])){
				$item = self::get((int) $b[0] & 0xFFFF, $meta);
			}elseif(defined(Item::class . "::" . strtoupper($b[0]))){
				$item = self::get(constant(Item::class . "::" . strtoupper($b[0])), $meta);
			}else{
				throw new InvalidArgumentException("Unable to resolve \"" . $str . "\" to a valid item");
			}

			return $item;
		}
	}
	public function __construct(int $id, int $meta = 0, int $count = 1, string $name = "Unknown"){
		$this->id = $id & 0xffff;
		$this->setDamage($meta);
		$this->count = $count;
		$this->name = $name;
		if(!isset($this->block) and $this->id <= 0xff and isset(BlockFactory::$list[$this->id])){
			$this->block = BlockFactory::get($this->id, $this->meta);
			$this->name = $this->block->getName();
		}
	}
	public function setCompoundTag($tags){
		if($tags instanceof CompoundTag){
			$this->setNamedTag($tags);
		}elseif(is_string($tags) and strlen($tags) > 0){
			$this->setNamedTag(self::parseCompoundTag($tags));
		}else{
			$this->clearNamedTag();
		}

		return $this;
	}
	public function getCompoundTag() : string{
		return $this->nbt !== null ? self::writeCompoundTag($this->nbt) : "";
	}
	public function hasCompoundTag() : bool{
		return $this->nbt !== null and $this->nbt->getCount() > 0;
	}
	public function hasCustomBlockData() : bool{
		return $this->getNamedTagEntry("BlockEntityTag") instanceof CompoundTag;
	}
	public function clearCustomBlockData(){
		$this->removeNamedTagEntry("BlockEntityTag");
		return $this;
	}
	public function setCustomBlockData(CompoundTag $compound){
		$tags = clone $compound;
		$tags->setName("BlockEntityTag");

		$this->setNamedTagEntry($tags);

		return $this;
	}
	public function getCustomBlockData(){
		$tag = $this->getNamedTagEntry("BlockEntityTag");
		return $tag instanceof CompoundTag ? $tag : null;
	}
	public function hasEnchantments() : bool{
		return $this->getNamedTagEntry("ench") instanceof ListTag;
	}
	public function getEnchantment(int $id){
		if(!$this->hasEnchantments()){
			return null;
		}

		foreach($this->getNamedTag()->ench as $entry){
			if($entry["id"] === $id){
				$e = Enchantment::getEnchantment($entry["id"]);
				$e->setLevel($entry["lvl"]);
				return $e;
			}
		}

		return null;
	}
	public function hasEnchantment(int $id, int $level = 1, bool $compareLevel = false) : bool{
		if($this->hasEnchantments()){
			foreach($this->getEnchantments() as $enchantment){
				if($enchantment->getId() == $id){
					if($compareLevel){
						if($enchantment->getLevel() == $level){
							return true;
						}
					}else{
						return true;
					}
				}
			}
		}
		return false;
	}
	public function getEnchantmentLevel(int $id){
		if(!$this->hasEnchantments()){
			return 0;
		}

		foreach($this->getNamedTag()->ench as $entry){
			if($entry["id"] === $id){
				$e = Enchantment::getEnchantment($entry["id"]);
				$e->setLevel($entry["lvl"]);
				$E_level = $e->getLevel() > Enchantment::getEnchantMaxLevel($id) ? Enchantment::getEnchantMaxLevel($id) : $e->getLevel();
				return $E_level;
			}
		}

		return 0;
	}
	public function removeEnchantment(int $id, int $level = -1){
		if(!$this->hasEnchantments()){
			return;
		}

		$tag = $this->getNamedTag();
		foreach($tag->ench as $k => $entry){
			if($entry["id"] === $id){
				if($level === -1 or $entry["lvl"] === $level){
					unset($tag->ench[$k]);
					break;
				}
			}
		}
		$this->setNamedTag($tag);
	}

	public function removeEnchantments(){
		$this->removeNamedTagEntry("ench");
	}
	public function addEnchantment(Enchantment $ench){
		$tag = $this->getNamedTag();

		if(!isset($tag->ench)){
			$tag->ench = new ListTag("ench", []);
			$tag->ench->setTagType(NBT::TAG_Compound);
		}

		$found = false;

		foreach ($tag->ench as $key => $entry) {
			if($entry["id"] === $ench->getId()) {
				$tag->ench->$key->lvl->setValue($ench->getLevel());
				$found = true;
				break;
			}
		}

		if(!$found) {
			$tag->ench->{count($tag->ench)} = new CompoundTag("", [
				"id" => new ShortTag("id", $ench->getId()),
				"lvl" => new ShortTag("lvl", $ench->getLevel())
			]);
		}

		$this->setNamedTag($tag);
	}
	public function getEnchantments() : array{
		if(!$this->hasEnchantments()){
			return [];
		}

		$enchantments = [];

		foreach($this->getNamedTag()->ench as $entry){
			$e = Enchantment::getEnchantment($entry["id"]);
			$e->setLevel($entry["lvl"]);
			$enchantments[] = $e;
		}

		return $enchantments;
	}
	public function hasRepairCost() : bool{
		$tag = $this->getNamedTag();
		if(isset($tag->RepairCost)){
			$tag = $tag->RepairCost;
			if($tag instanceof IntTag){
				return true;
			}
		}

		return false;
	}
	public function getRepairCost() : int{
		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->RepairCost;
			if($tag instanceof IntTag){
				return $tag->getValue();
			}
		}

		return 1;
	}
	public function setRepairCost(int $cost){
		if($cost === 1){
			$this->clearRepairCost();
		}

		$tag = $this->getNamedTag();
		$tag->RepairCost = new IntTag("RepairCost", $cost);

		$this->setCompoundTag($tag);

		return $this;
	}
	public function clearRepairCost(){
		$tag = $this->getNamedTag();

		if(isset($tag->RepairCost) and $tag->RepairCost instanceof IntTag){
			unset($tag->RepairCost);
			$this->setNamedTag($tag);
		}

		return $this;
	}
	public function hasCustomName() : bool{
		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof CompoundTag and isset($tag->Name) and $tag->Name instanceof StringTag){
				return true;
			}
		}

		return false;
	}
	public function getCustomName() : string{
		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof CompoundTag and isset($tag->Name) and $tag->Name instanceof StringTag){
				return $tag->Name->getValue();
			}
		}

		return "";
	}
	public function setCustomName(string $name){
		if($name === ""){
			return $this->clearCustomName();
		}

		$tag = $this->getNamedTag();
		if(isset($tag->display) and $tag->display instanceof CompoundTag){
			$tag->display->Name = new StringTag("Name", $name);
		}else{
			$tag->display = new CompoundTag("display", [
				new StringTag("Name", $name)
			]);
		}

		$this->setCompoundTag($tag);

		return $this;
	}
	public function clearCustomName(){
		$tag = $this->getNamedTag();

		if(isset($tag->display) and $tag->display instanceof CompoundTag){
			unset($tag->display->Name);
			if($tag->display->getCount() === 0){
				unset($tag->display);
			}

			$this->setNamedTag($tag);
		}

		return $this;
	}
	public function getLore() : array{
		$tag = $this->getNamedTagEntry("display");
		if($tag instanceof CompoundTag and isset($tag->Lore) and $tag->Lore instanceof ListTag){
			$lines = [];
			foreach($tag->Lore->getValue() as $line){
				$lines[] = $line->getValue();
			}
			return $lines;
		}
		return [];
	}
	public function setLore(array $lines){
		$tag = $this->getNamedTag();
		if(!isset($tag->display)){
			$tag->display = new CompoundTag("display", []);
		}
		$tag->display->Lore = new ListTag("Lore");
		$tag->display->Lore->setTagType(NBT::TAG_String);
		$count = 0;
		foreach($lines as $line){
			$tag->display->Lore[$count++] = new StringTag("", $line);
		}

		$this->setNamedTag($tag);

		return $this;
	}
	public function getNamedTagEntry(string $name) : ?NamedTag{
		return $this->getNamedTag()->{$name} ?? null;
	}

	public function setNamedTagEntry(NamedTag $new) : void{
		$tag = $this->getNamedTag();
		$tag->{$new->getName()} = $new;
		$this->setNamedTag($tag);
	}

	public function removeNamedTagEntry(string $name) : void{
		$tag = $this->getNamedTag();
		unset($tag->{$name});
		$this->setNamedTag($tag);
	}

	public function getNamedTag() : ?CompoundTag{
		return $this->nbt ?? ($this->nbt = new CompoundTag("", []));
	}
	public function setNamedTag(CompoundTag $tag){
		if($tag->getCount() === 0){
			return $this->clearNamedTag();
		}

		$this->nbt = clone $tag;

		return $this;
	}
	public function clearNamedTag(){
		$this->nbt = null;
		return $this;
	}
	public function getCount() : int{
		return $this->count;
	}
	public function setCount(int $count){
		$this->count = $count;
	}
	public function pop(int $count = 1) : Item{
		if($count > $this->count){
			throw new InvalidArgumentException("Cannot pop $count items from a stack of $this->count");
		}

		$item = clone $this;
		$item->count = $count;

		$this->count -= $count;

		return $item;
	}

	public function isNull() : bool{
		return $this->count <= 0 or $this->id === Item::AIR;
	}
	final public function getName() : string{
		return $this->hasCustomName() ? $this->getCustomName() : $this->getVanillaName();
	}
	public function getVanillaName() : string{
		return $this->name;
	}
	final public function canBePlaced() : bool{
		return $this->block !== null and $this->block->canBePlaced();
	}
	final public function isPlaceable() : bool{
		return $this->canBePlaced();
	}
	public function canBeConsumed() : bool{
		return false;
	}
	public function canBeConsumedBy(Entity $entity) : bool{
		return $this->canBeConsumed();
	}
	public function onConsume(Entity $entity){
	}
	public function getBlock() : Block{
		if($this->block instanceof Block){
			return clone $this->block;
		}else{
			return BlockFactory::get(self::AIR);
		}
	}
	final public function getId() : int{
		return $this->id;
	}
	final public function getDamage() : int{
		return $this->meta;
	}
	public function setDamage(int $meta){
		$this->meta = $meta !== -1 ? $meta & 0x7FFF : -1;

		return $this;
	}
	public function hasAnyDamageValue() : bool{
		return $this->meta === -1;
	}
	public function getMaxStackSize() : int{
		return 64;
	}
	final public function getFuelTime(){
		if(!isset(Fuel::$duration[$this->id])){
			return null;
		}
		if($this->id !== self::BUCKET or $this->meta === 10){
			return Fuel::$duration[$this->id];
		}

		return null;
	}
	public function getFuelResidue() : Item{
		$item = clone $this;
		$item->pop();

		return $item;
	}
	public function useOn($object){
		return false;
	}
	public function isTool(){
		return false;
	}
	public function getMaxDurability(){
		return false;
	}
	public function isPickaxe(){
		return false;
	}
	public function isAxe(){
		return false;
	}
	public function isSword(){
		return false;
	}
	public function isShovel(){
		return false;
	}
	public function isHoe(){
		return false;
	}
	public function isShears(){
		return false;
	}
	public function isArmor(){
		return false;
	}
	public function getArmorValue(){
		return false;
	}
	public function isBoots(){
		return false;
	}
	public function isHelmet(){
		return false;
	}
	public function isLeggings(){
		return false;
	}
	public function isChestplate(){
		return false;
	}
	public function getAttackDamage(){
		return 1;
	}
	public function getModifyAttackDamage(Entity $target){
		$rec = $this->getAttackDamage();
		$sharpL = $this->getEnchantmentLevel(Enchantment::TYPE_WEAPON_SHARPNESS);
		if($sharpL > 0){
			$rec += 0.5 * ($sharpL + 1);
		}

		if($target instanceof Skeleton or $target instanceof Zombie or
			$target instanceof Witch or $target instanceof PigZombie
		){
			$rec += 2.5 * $this->getEnchantmentLevel(Enchantment::TYPE_WEAPON_SMITE);

		}elseif($target instanceof Spider or $target instanceof CaveSpider or
			$target instanceof Silverfish
		){
			$rec += 2.5 * $this->getEnchantmentLevel(Enchantment::TYPE_WEAPON_ARTHROPODS);

		}
		return $rec;
	}
	public function getDestroySpeed(Block $block, Player $player){
		return 1;
	}

	public function useOnAir(Player $player) : void{

	}
	public function onActivate(Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return false;
	}
    public function getCooldownTicks() : int{
        return 0;
    }
	final public function equals(Item $item, bool $checkDamage = true, bool $checkCompound = true, bool $checkCount = false) : bool{
		return $this->id === $item->getId() and
			(!$checkDamage or $this->getDamage() === $item->getDamage()) and
			(!$checkCount or $this->getCount() === $item->getCount()) and
			(!$checkCompound or NBT::matchTree($this->getNamedTag(), $item->getNamedTag()));
	}
	final public function canStackWith(Item $other) : bool{
		return $this->equals($other, true, true);
	}
	final public function __toString() : string{
		return "Item " . $this->name . " (" . $this->id . ":" . ($this->hasAnyDamageValue() ? "?" : $this->meta) . ")x" . $this->count . ($this->hasCompoundTag() ? " tags:" . base64_encode($this->getCompoundTag()) : "");
	}
	final public function jsonSerialize(){
		$data = [
			"id" => $this->getId()
		];

		if($this->getDamage() !== 0){
			$data["damage"] = $this->getDamage();
		}

		if($this->getCount() !== 1){
			$data["count"] = $this->getCount();
		}

		if($this->hasCompoundTag()){
			$data["nbt_hex"] = bin2hex($this->getCompoundTag());
		}

		return $data;
	}
	final public static function jsonDeserialize(array $data) : Item{
		return Item::get(
			(int) $data["id"],
			(int) ($data["damage"] ?? 0),
			(int) ($data["count"] ?? 1),
			(string) ($data["nbt"] ?? (isset($data["nbt_hex"]) ? hex2bin($data["nbt_hex"]) : ""))
		);
	}
	public function nbtSerialize(int $slot = -1, string $tagName = "") : CompoundTag{
		$tag = new CompoundTag($tagName, [
			new ShortTag("id", Binary::signShort($this->id)),
			new ByteTag("Count", Binary::signByte($this->count)),
			new ShortTag("Damage", $this->meta),
		]);

		if($this->hasCompoundTag()){
			$tag->tag = clone $this->getNamedTag();
			$tag->tag->setName("tag");
		}

		if($slot !== -1){
			$tag->Slot = new ByteTag("Slot", $slot);
		}

		return $tag;
	}
	public static function nbtDeserialize(CompoundTag $tag) : Item{
		if(!isset($tag->id) or !isset($tag->Count)){
			return Item::get(0);
		}

		$count = Binary::unsignByte($tag->Count->getValue());
		$meta = isset($tag->Damage) ? $tag->Damage->getValue() : 0;

		if($tag->id instanceof ShortTag){
			$item = Item::get(Binary::unsignShort($tag->id->getValue()), $meta, $count);
		}elseif($tag->id instanceof StringTag){
			try{
				$item = Item::fromString($tag->id->getValue());
			}catch(\InvalidArgumentException $e){
				return Item::get(Item::AIR, 0, 0);
			}
			$item->setDamage($meta);
			$item->setCount($count);
		}else{
			throw new InvalidArgumentException("Item CompoundTag ID must be an instance of StringTag or ShortTag, " . get_class($tag->id) . " given");
		}

		if(isset($tag->tag) and $tag->tag instanceof CompoundTag){
			$t = clone $tag->tag;
			$t->setName("");
			$item->setNamedTag($t);
		}

		return $item;
	}
	public static function isRegistered(int $id) : bool{
		if($id < 256){
			return BlockFactory::isRegistered($id);
		}
		return self::$list[self::getListOffset($id)] !== null;
	}

	private static function getListOffset(int $id) : int{
		if($id < -0x8000 or $id > 0x7fff){
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		return $id & 0xffff;
	}

	public function __clone(){
		if($this->block !== null){
			$this->block = clone $this->block;
		}

		if($this->nbt !== null){
			$this->nbt = clone $this->nbt;
		}
	}
}