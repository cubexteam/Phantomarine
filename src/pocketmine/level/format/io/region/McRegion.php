<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\level\format\io\region;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\ChunkException;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\SubChunk;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\MainLogger;
use function array_filter;
use function array_values;
use function assert;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function is_int;
use function microtime;
use function mkdir;
use function pack;
use function rename;
use function scandir;
use function str_repeat;
use function strrpos;
use function substr;
use function time;
use function unpack;
use function zlib_decode;
use const SCANDIR_SORT_NONE;

class McRegion extends BaseLevelProvider{

	public const REGION_FILE_EXTENSION = "mcr";
	protected $regions = [];

	protected function nbtSerialize(Chunk $chunk) : string{
		$nbt = new CompoundTag("Level", []);
		$nbt->xPos = new IntTag("xPos", $chunk->getX());
		$nbt->zPos = new IntTag("zPos", $chunk->getZ());

		$nbt->V = new ByteTag("V", 0);
		$nbt->LastUpdate = new LongTag("LastUpdate", 0);
		$nbt->InhabitedTime = new LongTag("InhabitedTime", 0);
		$nbt->TerrainPopulated = new ByteTag("TerrainPopulated", $chunk->isPopulated());
		$nbt->LightPopulated = new ByteTag("LightPopulated", $chunk->isLightPopulated());

		$ids = "";
		$data = "";
		$skyLight = "";
		$blockLight = "";
		$subChunks = $chunk->getSubChunks();
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				for($y = 0; $y < 8; ++$y){
					$subChunk = $subChunks[$y];
					$ids .= $subChunk->getBlockIdColumn($x, $z);
					$data .= $subChunk->getBlockDataColumn($x, $z);
					$skyLight .= $subChunk->getBlockSkyLightColumn($x, $z);
					$blockLight .= $subChunk->getBlockLightColumn($x, $z);
				}
			}
		}

		$nbt->Blocks = new ByteArrayTag("Blocks", $ids);
		$nbt->Data = new ByteArrayTag("Data", $data);
		$nbt->SkyLight = new ByteArrayTag("SkyLight", $skyLight);
		$nbt->BlockLight = new ByteArrayTag("BlockLight", $blockLight);

		$nbt->Biomes = new ByteArrayTag("Biomes", $chunk->getBiomeIdArray());
		$nbt->HeightMap = new ByteArrayTag("HeightMap", pack("C*", ...$chunk->getHeightMapArray()));

		$entities = [];

		foreach($chunk->getSavableEntities() as $entity){
			$entity->saveNBT();
			$entities[] = $entity->namedtag;
		}

		$nbt->Entities = new ListTag("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);

		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new ListTag("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);


		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", [$nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}
	protected function nbtDeserialize(string $data){
		$data = @zlib_decode($data);
		if($data === false){
			throw new CorruptedChunkException("Failed to decompress chunk data");
		}
		$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->read($data);

			$chunk = $nbt->getData();

			if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
				throw new ChunkException("Invalid NBT format");
			}

			$chunk = $chunk->Level;

			$subChunks = [];
			$fullIds = isset($chunk->Blocks) ? $chunk->Blocks->getValue() : str_repeat("\x00", 32768);
            $fullData = isset($chunk->Data) ? $chunk->Data->getValue() : str_repeat("\x00", 16384);
			$fullSkyLight = isset($chunk->SkyLight) ? $chunk->SkyLight->getValue() : str_repeat("\xff", 16384);
            $fullBlockLight = isset($chunk->BlockLight) ? $chunk->BlockLight->getValue() : str_repeat("\x00", 16384);

			for($y = 0; $y < 8; ++$y){
				$offset = ($y << 4);
				$ids = "";
				for($i = 0; $i < 256; ++$i){
					$ids .= substr($fullIds, $offset, 16);
					$offset += 128;
				}
				$data = "";
				$offset = ($y << 3);
				for($i = 0; $i < 256; ++$i){
					$data .= substr($fullData, $offset, 8);
					$offset += 64;
				}
				$skyLight = "";
				$offset = ($y << 3);
				for($i = 0; $i < 256; ++$i){
					$skyLight .= substr($fullSkyLight, $offset, 8);
					$offset += 64;
				}
				$blockLight = "";
				$offset = ($y << 3);
				for($i = 0; $i < 256; ++$i){
					$blockLight .= substr($fullBlockLight, $offset, 8);
					$offset += 64;
				}
				$subChunks[$y] = new SubChunk($ids, $data, $skyLight, $blockLight);
			}

			if(isset($chunk->BiomeColors)){
				$biomeIds = ChunkUtils::convertBiomeColors($chunk->BiomeColors->getValue());
			}elseif(isset($chunk->Biomes)){
				$biomeIds = $chunk->Biomes->getValue();
			}else{
				$biomeIds = "";
			}

			$heightMap = [];
			if(isset($chunk->HeightMap)){
				if($chunk->HeightMap instanceof ByteArrayTag){
					$heightMap = array_values(unpack("C*", $chunk->HeightMap->getValue()));
				}elseif($chunk->HeightMap instanceof IntArrayTag){
					$heightMap = $chunk->HeightMap->getValue(); #blameshoghicp
				}
			}

			$result = new Chunk(
				(int) $chunk["xPos"],
				(int) $chunk["zPos"],
				$subChunks,
				isset($chunk->Entities) ? $chunk->Entities->getValue() : [],
				isset($chunk->TileEntities) ? $chunk->TileEntities->getValue() : [],
				$biomeIds,
				$heightMap
			);
			$result->setLightPopulated(isset($chunk->LightPopulated) ? ((bool) $chunk->LightPopulated->getValue()) : false);
			$result->setPopulated(isset($chunk->TerrainPopulated) ? ((bool) $chunk->TerrainPopulated->getValue()) : false);
			$result->setGenerated();
			$result->setChanged(false);
			return $result;
		}catch(\Throwable $e){
			MainLogger::getLogger()->logException($e);
			return null;
		}
	}

	public static function getProviderName() : string{
		return "mcregion";
	}
	public static function getPcWorldFormatVersion() : int{
		return 19132;
	}

	public function getWorldHeight() : int{
		return 128;
	}

	public static function isValid(string $path) : bool{
		$isValid = (file_exists($path . "/level.dat") and is_dir($path . "/region/"));

		if($isValid){
			$files = array_filter(scandir($path . "/region/", SCANDIR_SORT_NONE), function (string $file) : bool{
				$extPos = strrpos($file, ".");
				return $extPos !== false && substr($file, $extPos + 1, 2) === "mc";
			});

			foreach($files as $f){
				$extPos = strrpos($f, ".");
				if($extPos !== false && substr($f, $extPos + 1) !== static::REGION_FILE_EXTENSION){
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []){
		if(!file_exists($path)){
			mkdir($path, 0777, true);
		}

		if(!file_exists($path . "/region")){
			mkdir($path . "/region", 0777);
		}
		$levelData = new CompoundTag("Data", [
			new ByteTag("hardcore", ($options["hardcore"] ?? false) === true ? 1 : 0),
			new ByteTag("Difficulty", Level::getDifficultyFromString((string) ($options["difficulty"] ?? "normal"))),
			new ByteTag("initialized", 1),
			new IntTag("GameType", 0),
			new IntTag("generatorVersion", 1),
			new IntTag("SpawnX", 256),
			new IntTag("SpawnY", 70),
			new IntTag("SpawnZ", 256),
			new IntTag("version", static::getPcWorldFormatVersion()),
			new IntTag("DayTime", 0),
            new LongTag("LastPlayed", (int) (microtime(true) * 1000)),
			new LongTag("RandomSeed", $seed),
			new LongTag("SizeOnDisk", 0),
			new LongTag("Time", 0),
			new StringTag("generatorName", (string) GeneratorManager::getGeneratorName($generator)),
            new StringTag("generatorOptions", $options["preset"] ?? ""),
			new StringTag("LevelName", $name),
			new CompoundTag("GameRules", [])
		]);
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->setData(new CompoundTag("", [
			$levelData
		]));
		$buffer = $nbt->writeCompressed();
		file_put_contents($path . "level.dat", $buffer);
	}

	public function getGenerator() : string{
		return (string) $this->levelData["generatorName"];
	}

	public function getGeneratorOptions() : array{
		return ["preset" => $this->levelData["generatorOptions"]];
	}

	public function getDifficulty() : int{
		return isset($this->levelData->Difficulty) ? $this->levelData->Difficulty->getValue() : Level::DIFFICULTY_NORMAL;
	}

	public function setDifficulty(int $difficulty){
		$this->levelData->Difficulty = new ByteTag("Difficulty", $difficulty);
	}

	public function doGarbageCollection(){
		$limit = time() - 300;
		foreach($this->regions as $index => $region){
			if($region->lastUsed <= $limit){
				$region->close();
				unset($this->regions[$index]);
			}
		}
	}
	public static function getRegionIndex(int $chunkX, int $chunkZ, &$regionX, &$regionZ){
		$regionX = $chunkX >> 5;
		$regionZ = $chunkZ >> 5;
	}
	protected function getRegion(int $regionX, int $regionZ){
		return $this->regions[Level::chunkHash($regionX, $regionZ)] ?? null;
	}
	protected function pathToRegion(int $regionX, int $regionZ) : string{
		return $this->path . "region/r.$regionX.$regionZ." . static::REGION_FILE_EXTENSION;
	}
	protected function loadRegion(int $regionX, int $regionZ){
		if(!isset($this->regions[$index = Level::chunkHash($regionX, $regionZ)])){
			$path = $this->pathToRegion($regionX, $regionZ);

			$region = new RegionLoader($path, $regionX, $regionZ);
			try{
				$region->open();
			}catch(CorruptedRegionException $e){
				$logger = MainLogger::getLogger();
				$logger->error("Corrupted region file detected: " . $e->getMessage());

				$region->close(false);

				$backupPath = $path . ".bak." . time();
				rename($path, $backupPath);
				$logger->error("Corrupted region file has been backed up to " . $backupPath);

				$region = new RegionLoader($path, $regionX, $regionZ);
				$region->open();
			}

			$this->regions[$index] = $region;
		}
	}

	public function close(){
		foreach($this->regions as $index => $region){
			$region->close();
			unset($this->regions[$index]);
		}
	}
	protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$regionX = $regionZ = null;
		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		assert(is_int($regionX) and is_int($regionZ));

		if(!file_exists($this->pathToRegion($regionX, $regionZ))){
			return null;
		}
		$this->loadRegion($regionX, $regionZ);

		$chunkData = $this->getRegion($regionX, $regionZ)->readChunk($chunkX & 0x1f, $chunkZ & 0x1f);
		if($chunkData !== null){
			return $this->nbtDeserialize($chunkData);
		}

		return null;
	}

	protected function writeChunk(Chunk $chunk) : void{
		$chunkX = $chunk->getX();
		$chunkZ = $chunk->getZ();

		self::getRegionIndex($chunkX, $chunkZ, $regionX, $regionZ);
		$this->loadRegion($regionX, $regionZ);

		$this->getRegion($regionX, $regionZ)->writeChunk($chunkX & 0x1f, $chunkZ & 0x1f, $this->nbtSerialize($chunk));
	}
}