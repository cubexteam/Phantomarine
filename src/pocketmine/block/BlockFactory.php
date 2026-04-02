<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\block;

use pocketmine\level\Position;
class BlockFactory{
	public static $list = null;
	public static $fullList = null;
	public static $solid = null;
	public static $transparent = null;
	public static $hardness = null;
	public static $light = null;
	public static $lightFilter = null;
	public static $diffusesSkyLight = null;
	public static $blastResistance = null;
	public static function init(bool $force = false){
		if(self::$list === null){
			self::$list = new \SplFixedArray(256);
			self::$fullList = new \SplFixedArray(4096);
			self::$light = new \SplFixedArray(256);
			self::$lightFilter = new \SplFixedArray(256);
			self::$solid = new \SplFixedArray(256);
			self::$hardness = new \SplFixedArray(256);
			self::$transparent = new \SplFixedArray(256);
			self::$diffusesSkyLight = new \SplFixedArray(256);
			self::$blastResistance = new \SplFixedArray(256);
			self::$list[BlockIds::AIR] = Air::class;
			self::$list[BlockIds::STONE] = Stone::class;
			self::$list[BlockIds::GRASS] = Grass::class;
			self::$list[BlockIds::DIRT] = Dirt::class;
			self::$list[BlockIds::COBBLESTONE] = Cobblestone::class;
			self::$list[BlockIds::PLANKS] = Planks::class;
			self::$list[BlockIds::SAPLING] = Sapling::class;
			self::$list[BlockIds::BEDROCK] = Bedrock::class;
			self::$list[BlockIds::WATER] = Water::class;
			self::$list[BlockIds::STILL_WATER] = StillWater::class;
			self::$list[BlockIds::LAVA] = Lava::class;
			self::$list[BlockIds::STILL_LAVA] = StillLava::class;
			self::$list[BlockIds::SAND] = Sand::class;
			self::$list[BlockIds::GRAVEL] = Gravel::class;
			self::$list[BlockIds::GOLD_ORE] = GoldOre::class;
			self::$list[BlockIds::IRON_ORE] = IronOre::class;
			self::$list[BlockIds::COAL_ORE] = CoalOre::class;
			self::$list[BlockIds::WOOD] = Wood::class;
			self::$list[BlockIds::LEAVES] = Leaves::class;
			self::$list[BlockIds::SPONGE] = Sponge::class;
			self::$list[BlockIds::GLASS] = Glass::class;
			self::$list[BlockIds::LAPIS_ORE] = LapisOre::class;
			self::$list[BlockIds::LAPIS_BLOCK] = Lapis::class;
			self::$list[BlockIds::SANDSTONE] = Sandstone::class;
			self::$list[BlockIds::RED_SANDSTONE] = RedSandstone::class;
			self::$list[BlockIds::RED_SANDSTONE_STAIRS] = RedSandstoneStairs::class;
			self::$list[BlockIds::BED_BLOCK] = Bed::class;
			self::$list[BlockIds::COBWEB] = Cobweb::class;
			self::$list[BlockIds::TALL_GRASS] = TallGrass::class;
			self::$list[BlockIds::DEAD_BUSH] = DeadBush::class;
			self::$list[BlockIds::WOOL] = Wool::class;
			self::$list[BlockIds::DANDELION] = Dandelion::class;
			self::$list[BlockIds::RED_FLOWER] = Flower::class;
			self::$list[BlockIds::BROWN_MUSHROOM] = BrownMushroom::class;
			self::$list[BlockIds::RED_MUSHROOM] = RedMushroom::class;
			self::$list[BlockIds::GOLD_BLOCK] = Gold::class;
			self::$list[BlockIds::IRON_BLOCK] = Iron::class;
			self::$list[BlockIds::DOUBLE_SLAB] = DoubleSlab::class;
			self::$list[BlockIds::SLAB] = Slab::class;
			self::$list[BlockIds::RED_SANDSTONE_SLAB] = RedSandstoneSlab::class;
			self::$list[BlockIds::DOUBLE_RED_SANDSTONE_SLAB] = DoubleRedSandstoneSlab::class;
			self::$list[BlockIds::BRICKS_BLOCK] = Bricks::class;
			self::$list[BlockIds::TNT] = TNT::class;
			self::$list[BlockIds::BOOKSHELF] = Bookshelf::class;
			self::$list[BlockIds::MOSS_STONE] = MossStone::class;
			self::$list[BlockIds::OBSIDIAN] = Obsidian::class;
			self::$list[BlockIds::TORCH] = Torch::class;
			self::$list[BlockIds::FIRE] = Fire::class;
			self::$list[BlockIds::MONSTER_SPAWNER] = MonsterSpawner::class;
			self::$list[BlockIds::WOOD_STAIRS] = WoodStairs::class;
			self::$list[BlockIds::ENDER_CHEST] = EnderChest::class;
			self::$list[BlockIds::CHEST] = Chest::class;

			self::$list[BlockIds::DIAMOND_ORE] = DiamondOre::class;
			self::$list[BlockIds::DIAMOND_BLOCK] = Diamond::class;
			self::$list[BlockIds::WORKBENCH] = Workbench::class;
			self::$list[BlockIds::WHEAT_BLOCK] = Wheat::class;
			self::$list[BlockIds::FARMLAND] = Farmland::class;
			self::$list[BlockIds::FURNACE] = Furnace::class;
			self::$list[BlockIds::BURNING_FURNACE] = BurningFurnace::class;
			self::$list[BlockIds::SIGN_POST] = SignPost::class;
			self::$list[BlockIds::WOOD_DOOR_BLOCK] = WoodDoor::class;
			self::$list[BlockIds::SPRUCE_DOOR_BLOCK] = SpruceDoor::class;
			self::$list[BlockIds::BIRCH_DOOR_BLOCK] = BirchDoor::class;
			self::$list[BlockIds::JUNGLE_DOOR_BLOCK] = JungleDoor::class;
			self::$list[BlockIds::ACACIA_DOOR_BLOCK] = AcaciaDoor::class;
			self::$list[BlockIds::DARK_OAK_DOOR_BLOCK] = DarkOakDoor::class;
			self::$list[BlockIds::LADDER] = Ladder::class;

			self::$list[BlockIds::COBBLESTONE_STAIRS] = CobblestoneStairs::class;
			self::$list[BlockIds::WALL_SIGN] = WallSign::class;

			self::$list[BlockIds::IRON_DOOR_BLOCK] = IronDoor::class;
			self::$list[BlockIds::REDSTONE_ORE] = RedstoneOre::class;
			self::$list[BlockIds::GLOWING_REDSTONE_ORE] = GlowingRedstoneOre::class;

			self::$list[BlockIds::SNOW_LAYER] = SnowLayer::class;
			self::$list[BlockIds::ICE] = Ice::class;
			self::$list[BlockIds::SNOW_BLOCK] = Snow::class;
			self::$list[BlockIds::CACTUS] = Cactus::class;
			self::$list[BlockIds::CLAY_BLOCK] = Clay::class;
			self::$list[BlockIds::SUGARCANE_BLOCK] = Sugarcane::class;

			self::$list[BlockIds::FENCE] = Fence::class;
			self::$list[BlockIds::PUMPKIN] = Pumpkin::class;
			self::$list[BlockIds::NETHERRACK] = Netherrack::class;
			self::$list[BlockIds::SOUL_SAND] = SoulSand::class;
			self::$list[BlockIds::GLOWSTONE_BLOCK] = Glowstone::class;

			self::$list[BlockIds::LIT_PUMPKIN] = LitPumpkin::class;
			self::$list[BlockIds::CAKE_BLOCK] = Cake::class;

			self::$list[BlockIds::TRAPDOOR] = Trapdoor::class;
			self::$list[BlockIds::IRON_TRAPDOOR] = IronTrapdoor::class;

			self::$list[BlockIds::STONE_BRICKS] = StoneBricks::class;

			self::$list[BlockIds::BROWN_MUSHROOM_BLOCK] = BrownMushroomBlock::class;
			self::$list[BlockIds::RED_MUSHROOM_BLOCK] = RedMushroomBlock::class;

			self::$list[BlockIds::IRON_BARS] = IronBars::class;
			self::$list[BlockIds::GLASS_PANE] = GlassPane::class;
			self::$list[BlockIds::MELON_BLOCK] = Melon::class;
			self::$list[BlockIds::PUMPKIN_STEM] = PumpkinStem::class;
			self::$list[BlockIds::MELON_STEM] = MelonStem::class;
			self::$list[BlockIds::VINE] = Vine::class;
			self::$list[BlockIds::FENCE_GATE] = FenceGate::class;
			self::$list[BlockIds::BRICK_STAIRS] = BrickStairs::class;
			self::$list[BlockIds::STONE_BRICK_STAIRS] = StoneBrickStairs::class;

			self::$list[BlockIds::MYCELIUM] = Mycelium::class;
			self::$list[BlockIds::WATER_LILY] = WaterLily::class;
			self::$list[BlockIds::NETHER_BRICKS] = NetherBrick::class;

			self::$list[BlockIds::PORTAL] = Portal::class;
			self::$list[BlockIds::NETHER_BRICKS_STAIRS] = NetherBrickStairs::class;
			self::$list[BlockIds::NETHER_WART_BLOCK] = NetherWart::class;
			self::$list[BlockIds::ENCHANTING_TABLE] = EnchantingTable::class;

			self::$list[BlockIds::BREWING_STAND_BLOCK] = BrewingStand::class;
			self::$list[BlockIds::END_PORTAL] = EndPortal::class;
			self::$list[BlockIds::END_PORTAL_FRAME] = EndPortalFrame::class;
			self::$list[BlockIds::END_STONE] = EndStone::class;

			self::$list[BlockIds::END_STONE_BRICKS] = EndStoneBricks::class;
			self::$list[BlockIds::END_ROD] = EndRod::class;

			self::$list[BlockIds::PURPUR] = Purpur::class;
			self::$list[BlockIds::PURPUR_STAIRS] = PurpurStairs::class;

			self::$list[BlockIds::CHORUS_FLOWER] = ChorusFlower::class;
			self::$list[BlockIds::CHORUS_PLANT] = ChorusPlant::class;

			self::$list[BlockIds::SANDSTONE_STAIRS] = SandstoneStairs::class;
			self::$list[BlockIds::EMERALD_ORE] = EmeraldOre::class;

			self::$list[BlockIds::EMERALD_BLOCK] = Emerald::class;
			self::$list[BlockIds::SPRUCE_WOOD_STAIRS] = SpruceWoodStairs::class;
			self::$list[BlockIds::BIRCH_WOOD_STAIRS] = BirchWoodStairs::class;
			self::$list[BlockIds::JUNGLE_WOOD_STAIRS] = JungleWoodStairs::class;
			self::$list[BlockIds::BEACON] = Beacon::class;
			self::$list[BlockIds::STONE_WALL] = StoneWall::class;

			self::$list[BlockIds::FLOWER_POT_BLOCK] = FlowerPot::class;
			self::$list[BlockIds::CARROT_BLOCK] = Carrot::class;
			self::$list[BlockIds::POTATO_BLOCK] = Potato::class;
			self::$list[BlockIds::ANVIL] = Anvil::class;

			self::$list[BlockIds::TRAPPED_CHEST] = TrappedChest::class;
			self::$list[BlockIds::REDSTONE_BLOCK] = Redstone::class;

			self::$list[BlockIds::SHULKER_BOX] = ShulkerBox::class;

			self::$list[BlockIds::QUARTZ_BLOCK] = Quartz::class;
			self::$list[BlockIds::QUARTZ_STAIRS] = QuartzStairs::class;
			self::$list[BlockIds::DOUBLE_WOOD_SLAB] = DoubleWoodSlab::class;
			self::$list[BlockIds::WOOD_SLAB] = WoodSlab::class;
			self::$list[BlockIds::STAINED_CLAY] = StainedClay::class;

			self::$list[BlockIds::LEAVES2] = Leaves2::class;
			self::$list[BlockIds::WOOD2] = Wood2::class;
			self::$list[BlockIds::ACACIA_WOOD_STAIRS] = AcaciaWoodStairs::class;
			self::$list[BlockIds::DARK_OAK_WOOD_STAIRS] = DarkOakWoodStairs::class;

			self::$list[BlockIds::SLIME_BLOCK] = SlimeBlock::class;
			self::$list[BlockIds::PRISMARINE] = Prismarine::class;
			self::$list[BlockIds::SEA_LANTERN] = SeaLantern::class;
			self::$list[BlockIds::HAY_BALE] = HayBale::class;
			self::$list[BlockIds::CARPET] = Carpet::class;
			self::$list[BlockIds::HARDENED_CLAY] = HardenedClay::class;
			self::$list[BlockIds::COAL_BLOCK] = Coal::class;

			self::$list[BlockIds::PACKED_ICE] = PackedIce::class;
			self::$list[BlockIds::DOUBLE_PLANT] = DoublePlant::class;

			self::$list[BlockIds::FENCE_GATE_SPRUCE] = FenceGateSpruce::class;
			self::$list[BlockIds::FENCE_GATE_BIRCH] = FenceGateBirch::class;
			self::$list[BlockIds::FENCE_GATE_JUNGLE] = FenceGateJungle::class;
			self::$list[BlockIds::FENCE_GATE_DARK_OAK] = FenceGateDarkOak::class;
			self::$list[BlockIds::FENCE_GATE_ACACIA] = FenceGateAcacia::class;

			self::$list[BlockIds::GRASS_PATH] = GrassPath::class;

			self::$list[BlockIds::PODZOL] = Podzol::class;
			self::$list[BlockIds::BEETROOT_BLOCK] = Beetroot::class;
			self::$list[BlockIds::STONECUTTER] = Stonecutter::class;
			self::$list[BlockIds::GLOWING_OBSIDIAN] = GlowingObsidian::class;
			self::$list[BlockIds::NETHER_REACTOR] = NetherReactor::class;
			self::$list[BlockIds::CONCRETE] = Concrete::class;
			self::$list[BlockIds::CONCRETE_POWDER] = ConcretePowder::class;

			self::$list[BlockIds::BLACK_GLAZED_TERRACOTTA] = BlackGlazedTerracotta::class;
			self::$list[BlockIds::BLUE_GLAZED_TERRACOTTA] = BlueGlazedTerracotta::class;
			self::$list[BlockIds::BROWN_GLAZED_TERRACOTTA] = BrownGlazedTerracotta::class;
			self::$list[BlockIds::CYAN_GLAZED_TERRACOTTA] = CyanGlazedTerracotta::class;
			self::$list[BlockIds::GRAY_GLAZED_TERRACOTTA] = GrayGlazedTerracotta::class;
			self::$list[BlockIds::GREEN_GLAZED_TERRACOTTA] = GreenGlazedTerracotta::class;
			self::$list[BlockIds::LIGHT_BLUE_GLAZED_TERRACOTTA] = LightBlueGlazedTerracotta::class;
			self::$list[BlockIds::LIME_GLAZED_TERRACOTTA] = LimeGlazedTerracotta::class;
			self::$list[BlockIds::MAGENTA_GLAZED_TERRACOTTA] = MagentaGlazedTerracotta::class;
			self::$list[BlockIds::ORANGE_GLAZED_TERRACOTTA] = OrangeGlazedTerracotta::class;
			self::$list[BlockIds::PINK_GLAZED_TERRACOTTA] = PinkGlazedTerracotta::class;
			self::$list[BlockIds::PURPLE_GLAZED_TERRACOTTA] = PurpleGlazedTerracotta::class;
			self::$list[BlockIds::RED_GLAZED_TERRACOTTA] = RedGlazedTerracotta::class;
			self::$list[BlockIds::SILVER_GLAZED_TERRACOTTA] = SilverGlazedTerracotta::class;
			self::$list[BlockIds::WHITE_GLAZED_TERRACOTTA] = WhiteGlazedTerracotta::class;
			self::$list[BlockIds::YELLOW_GLAZED_TERRACOTTA] = YellowGlazedTerracotta::class;

			self::$list[BlockIds::NETHER_BRICK_FENCE] = NetherBrickFence::class;
			self::$list[BlockIds::POWERED_RAIL] = PoweredRail::class;
			self::$list[BlockIds::RAIL] = Rail::class;

			self::$list[BlockIds::WOODEN_PRESSURE_PLATE] = WoodenPressurePlate::class;
			self::$list[BlockIds::STONE_PRESSURE_PLATE] = StonePressurePlate::class;
			self::$list[BlockIds::LIGHT_WEIGHTED_PRESSURE_PLATE] = LightWeightedPressurePlate::class;
			self::$list[BlockIds::HEAVY_WEIGHTED_PRESSURE_PLATE] = HeavyWeightedPressurePlate::class;
			self::$list[BlockIds::REDSTONE_WIRE] = RedstoneWire::class;
			self::$list[BlockIds::ACTIVE_REDSTONE_LAMP] = ActiveRedstoneLamp::class;
			self::$list[BlockIds::INACTIVE_REDSTONE_LAMP] = InactiveRedstoneLamp::class;
			self::$list[BlockIds::LIT_REDSTONE_LAMP] = LitRedstoneLamp::class;
			self::$list[BlockIds::REDSTONE_LAMP] = RedstoneLamp::class;
			self::$list[BlockIds::REDSTONE_TORCH] = RedstoneTorch::class;
			self::$list[BlockIds::WOODEN_BUTTON] = WoodenButton::class;
			self::$list[BlockIds::STONE_BUTTON] = StoneButton::class;
			self::$list[BlockIds::LEVER] = Lever::class;
			self::$list[BlockIds::DAYLIGHT_SENSOR] = DaylightDetector::class;
			self::$list[BlockIds::DAYLIGHT_SENSOR_INVERTED] = DaylightDetectorInverted::class;
			self::$list[BlockIds::NOTEBLOCK] = Noteblock::class;
			self::$list[BlockIds::SKULL_BLOCK] = SkullBlock::class;
			self::$list[BlockIds::NETHER_QUARTZ_ORE] = NetherQuartzOre::class;
			self::$list[BlockIds::ACTIVATOR_RAIL] = ActivatorRail::class;
			self::$list[BlockIds::COCOA_BLOCK] = CocoaBlock::class;
			self::$list[BlockIds::DETECTOR_RAIL] = DetectorRail::class;
			self::$list[BlockIds::TRIPWIRE] = Tripwire::class;
			self::$list[BlockIds::TRIPWIRE_HOOK] = TripwireHook::class;
			self::$list[BlockIds::ITEM_FRAME_BLOCK] = ItemFrame::class;
			self::$list[BlockIds::DISPENSER] = Dispenser::class;

			self::$list[BlockIds::PISTON] = Piston::class;
			self::$list[BlockIds::STICKY_PISTON] = StickyPiston::class;
			self::$list[BlockIds::PISTON_HEAD] = PistonHead::class;

			self::$list[BlockIds::DROPPER] = Dropper::class;
			self::$list[BlockIds::POWERED_REPEATER_BLOCK] = PoweredRepeater::class;
			self::$list[BlockIds::UNPOWERED_REPEATER_BLOCK] = UnpoweredRepeater::class;
			self::$list[BlockIds::CAULDRON_BLOCK] = Cauldron::class;
			self::$list[BlockIds::INVISIBLE_BEDROCK] = InvisibleBedrock::class;
			self::$list[BlockIds::HOPPER_BLOCK] = Hopper::class;
			self::$list[BlockIds::DRAGON_EGG] = DragonEgg::class;
			self::$list[BlockIds::COMMAND_BLOCK] = CommandBlock::class;

			foreach(self::$list as $id => $class){
				if($class !== null){
					$block = new $class();

					for($data = 0; $data < 16; ++$data){
						self::$fullList[($id << 4) | $data] = new $class($data);
					}
				}else{
					$block = new UnknownBlock($id);

					for($data = 0; $data < 16; ++$data){
						self::$fullList[($id << 4) | $data] = new UnknownBlock($id, $data);
					}
				}

				self::$solid[$id] = $block->isSolid();
				self::$transparent[$id] = $block->isTransparent();
				self::$hardness[$id] = $block->getHardness();
				self::$light[$id] = $block->getLightLevel();
				self::$lightFilter[$id] = min(15, $block->getLightFilter() + 1);
				self::$diffusesSkyLight[$id] = $block->diffusesSkyLight();
				self::$blastResistance[$id] = $block->getResistance();
			}
		}
	}
	public static function registerBlock(Block $block, bool $override = false){
		$id = $block->getId();

		if(self::$list[$id] !== null and !(self::$list[$id] instanceof UnknownBlock) and !$override){
			throw new \RuntimeException("Trying to overwrite an already registered block");
		}

		self::$list[$id] = clone $block;

		for($meta = 0; $meta < 16; ++$meta){
			$variant = clone $block;
			$variant->setDamage($meta);
			self::$fullList[($id << 4) | $meta] = $variant;
		}

		self::$solid[$id] = $block->isSolid();
		self::$transparent[$id] = $block->isTransparent();
		self::$hardness[$id] = $block->getHardness();
		self::$light[$id] = $block->getLightLevel();
		self::$lightFilter[$id] = $block->getLightFilter() + 1;
		self::$diffusesSkyLight[$id] = $block->diffusesSkyLight();
		self::$blastResistance[$id] = $block->getBlastResistance();
	}

	public static function isInit() : bool{
		return self::$fullList !== null;
	}
	public static function isRegistered(int $id) : bool{
		$b = self::$fullList[$id << 4];
		return $b !== null and !($b instanceof UnknownBlock);
	}
	public static function get(int $id, int $meta = 0, Position $pos = null) : Block{
		if($id > 0xff){
			trigger_error("BlockID cannot be higher than 255, defaulting to 0", E_USER_NOTICE);
			$id = 0;
		}

		try{
			$block = self::$fullList[($id << 4) | $meta];
			if($block !== null){
				$block = clone $block;
			}else{
				$block = new UnknownBlock($id, $meta);
			}
		}catch(\RuntimeException $e){
			$block = new UnknownBlock($id, $meta);
		}

		if($pos !== null){
			$block->x = $pos->getFloorX();
			$block->y = $pos->getFloorY();
			$block->z = $pos->getFloorZ();
			$block->level = $pos->level;
		}

		return $block;
	}
}