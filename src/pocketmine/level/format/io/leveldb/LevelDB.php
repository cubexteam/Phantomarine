<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\format\io\leveldb;

use pocketmine\level\format\Chunk;
use pocketmine\level\format\io\BaseLevelProvider;
use pocketmine\level\format\io\ChunkUtils;
use pocketmine\level\format\io\exception\CorruptedChunkException;
use pocketmine\level\format\io\exception\UnsupportedChunkFormatException;
use pocketmine\level\format\SubChunk;
use pocketmine\level\generator\Flat;
use pocketmine\level\generator\GeneratorManager;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\MainLogger;
use const LEVELDB_ZLIB_RAW_COMPRESSION;

class LevelDB extends BaseLevelProvider{

	const TAG_DATA_2D = "\x2d";
	const TAG_DATA_2D_LEGACY = "\x2e";
	const TAG_SUBCHUNK_PREFIX = "\x2f";
	const TAG_LEGACY_TERRAIN = "0";
	const TAG_BLOCK_ENTITY = "1";
	const TAG_ENTITY = "2";
	const TAG_PENDING_TICK = "3";
	const TAG_BLOCK_EXTRA_DATA = "4";
	const TAG_BIOME_STATE = "5";
    const TAG_STATE_FINALISATION = "6";

	const TAG_BORDER_BLOCKS = "8";
	const TAG_HARDCODED_SPAWNERS = "9";

    const FINALISATION_NEEDS_INSTATICKING = 0;
    const FINALISATION_NEEDS_POPULATION = 1;
    const FINALISATION_DONE = 2;

	const TAG_VERSION = "v";

	const ENTRY_FLAT_WORLD_LAYERS = "game_flatworldlayers";

	const GENERATOR_LIMITED = 0;
	const GENERATOR_INFINITE = 1;
	const GENERATOR_FLAT = 2;

	const CURRENT_STORAGE_VERSION = 5;
    const CURRENT_LEVEL_CHUNK_VERSION = 4;
    const CURRENT_LEVEL_SUBCHUNK_VERSION = 0;
	protected $db;

	private static function checkForLevelDBExtension(){
		if(!extension_loaded('leveldb')){
			throw new LevelException("The leveldb PHP extension is required to use this world format");
		}

		if(!defined('LEVELDB_ZLIB_RAW_COMPRESSION')){
			throw new LevelException("Given version of php-leveldb doesn't support zlib raw compression");
		}
	}

	private static function createDB(string $path) : \LevelDB{
		return new \LevelDB($path . "/db", [
			"compression" => LEVELDB_ZLIB_RAW_COMPRESSION,
			"block_size" => 64 * 1024
		]);
	}
	public function __construct(string $path){
		self::checkForLevelDBExtension();
		parent::__construct($path);

		$this->db = self::createDB($path);
	}

	protected function loadLevelData() : void{
		$rawLevelData = file_get_contents($this->getPath() . "level.dat");
		if($rawLevelData === false or strlen($rawLevelData) <= 8){
			throw new LevelException("Truncated level.dat");
		}
		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$nbt->read(substr($rawLevelData, 8));
		$levelData = $nbt->getData();
		if($levelData instanceof CompoundTag){
			$this->levelData = $levelData;
		}else{
			throw new LevelException("Invalid level.dat");
		}

		if(isset($this->levelData->StorageVersion) and $this->levelData->StorageVersion->getValue() > self::CURRENT_STORAGE_VERSION){
			throw new LevelException("Specified LevelDB world format version is newer than the version supported by the server");
		}
	}

	protected function fixLevelData() : void{
		$db = self::createDB($this->path);

		if(!isset($this->levelData->generatorName)){
			if(isset($this->levelData->Generator)){
				switch((int) $this->levelData->Generator->getValue()){
					case self::GENERATOR_FLAT:
						$this->levelData->generatorName = new StringTag("generatorName", "flat");
						if(($layers = $db->get(self::ENTRY_FLAT_WORLD_LAYERS)) !== false){
							$layers = trim($layers, "[]");
						}else{
							$layers = "7,3,3,2";
						}
						$this->levelData->generatorOptions = new StringTag("generatorOptions", "2;" . $layers . ";1");
						break;
					case self::GENERATOR_INFINITE:
						$this->levelData->generatorName = new StringTag("generatorName", "default");
						$this->levelData->generatorOptions = new StringTag("generatorOptions", "");
						break;
					case self::GENERATOR_LIMITED:
						throw new LevelException("Limited worlds are not currently supported");
					default:
						throw new LevelException("Unknown LevelDB world format type, this level cannot be loaded");
				}
			}else{
				$this->levelData->generatorName = new StringTag("generatorName", "default");
			}
		}elseif(($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($this->levelData->generatorName)) !== null){
			$this->levelData->generatorName = new StringTag("generatorName", (string) $generatorName);
		}

		if(!isset($this->levelData->generatorOptions)){
			$this->levelData->generatorOptions = new StringTag("generatorOptions", "");
		}
	}
	public static function getProviderName() : string{
		return "leveldb";
	}
	public function getWorldHeight() : int{
		return 256;
	}
	public static function isValid(string $path) : bool{
		return file_exists($path . "/level.dat") and is_dir($path . "/db/");
	}
	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []){
		self::checkForLevelDBExtension();

		if(!file_exists($path . "/db")){
			mkdir($path . "/db", 0777, true);
		}

		switch($generator){
			case Flat::class:
				$generatorType = self::GENERATOR_FLAT;
				break;
			default:
				$generatorType = self::GENERATOR_INFINITE;
		}

		$levelData = new CompoundTag("", [
			new IntTag("DayCycleStopTime", -1),
			new IntTag("Difficulty", Level::getDifficultyFromString((string) ($options["difficulty"] ?? "normal"))),
			new ByteTag("ForceGameType", 0),
			new IntTag("GameType", 0),
			new IntTag("Generator", $generatorType),
			new LongTag("LastPlayed", time()),
			new StringTag("LevelName", $name),
			new IntTag("NetworkVersion", ProtocolInfo::CURRENT_PROTOCOL),
			new LongTag("RandomSeed", $seed),
			new IntTag("SpawnX", 0),
			new IntTag("SpawnY", 32767),
			new IntTag("SpawnZ", 0),
			new IntTag("StorageVersion", self::CURRENT_STORAGE_VERSION),
			new LongTag("Time", 0),
			new ByteTag("eduLevel", 0),
			new ByteTag("falldamage", 1),
			new ByteTag("firedamage", 1),
			new ByteTag("hasBeenLoadedInCreative", 1),
			new ByteTag("immutableWorld", 0),
			new FloatTag("lightningLevel", 0.0),
			new IntTag("lightningTime", 0),
			new ByteTag("pvp", 1),
			new FloatTag("rainLevel", 0.0),
			new IntTag("rainTime", 0),
			new ByteTag("spawnMobs", 1),
			new ByteTag("texturePacksRequired", 0),

			new CompoundTag("GameRules", []),
			new ByteTag("hardcore", ($options["hardcore"] ?? false) === true ? 1 : 0),
			new StringTag("generatorName", (string) GeneratorManager::getGeneratorName($generator)),
			new StringTag("generatorOptions", $options["preset"] ?? "")
		]);

		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$nbt->setData($levelData);
		$buffer = $nbt->write();
		file_put_contents($path . "level.dat", Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);

		$db = self::createDB($path);

		if($generatorType === self::GENERATOR_FLAT and isset($options["preset"])){
			$layers = explode(";", $options["preset"])[1] ?? "";
			if($layers !== ""){
				$out = "[";
				foreach(Flat::parseLayers($layers) as $result){
					$out .= $result[0] . ",";
				}
				$out = rtrim($out, ",") . "]";
				$db->put(self::ENTRY_FLAT_WORLD_LAYERS, $out);
			}
		}
	}

	public function saveLevelData(){
        $this->levelData->NetworkVersion = new IntTag("NetworkVersion", ProtocolInfo::CURRENT_PROTOCOL);
        $this->levelData->StorageVersion = new IntTag("StorageVersion", self::CURRENT_STORAGE_VERSION);

		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$nbt->setData($this->levelData);
		$buffer = $nbt->write();
		file_put_contents($this->getPath() . "level.dat", Binary::writeLInt(self::CURRENT_STORAGE_VERSION) . Binary::writeLInt(strlen($buffer)) . $buffer);
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
		$this->levelData->Difficulty = new IntTag("Difficulty", $difficulty);
	}
	protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk{
		$index = LevelDB::chunkIndex($chunkX, $chunkZ);

		$chunkVersionRaw = $this->db->get($index . self::TAG_VERSION);
		if($chunkVersionRaw === false){
			return null;
		}

		try{
			$subChunks = [];
			$heightMap = [];
			$biomeIds = "";
            $lightPopulated = true;

			$chunkVersion = ord($chunkVersionRaw);
			$hasBeenUpgraded = $chunkVersion < self::CURRENT_LEVEL_CHUNK_VERSION;

            $binaryStream = new BinaryStream();

            switch($chunkVersion){
				case 7:
                case 4:
                case 3:
                    for($y = 0; $y < Chunk::MAX_SUBCHUNKS; ++$y){
                        if(($data = $this->db->get($index . self::TAG_SUBCHUNK_PREFIX . chr($y))) === false){
                            continue;
                        }

                        $binaryStream->setBuffer($data, 0);
                        $subChunkVersion = $binaryStream->getByte();
						if($subChunkVersion < self::CURRENT_LEVEL_SUBCHUNK_VERSION){
							$hasBeenUpgraded = true;
						}

                        switch($subChunkVersion){
                            case 0:
                                $blocks = $binaryStream->get(4096);
                                $blockData = $binaryStream->get(2048);
                                if($chunkVersion < 4){
                                    $blockSkyLight = $binaryStream->get(2048);
                                    $blockLight = $binaryStream->get(2048);
									$hasBeenUpgraded = true;
                                }else{
                                    $blockSkyLight = "";
                                    $blockLight = "";
                                    $lightPopulated = false;
                                }

                                $subChunks[$y] = new SubChunk($blocks, $blockData, $blockSkyLight, $blockLight);
                                break;
                            default:
                                throw new UnsupportedChunkFormatException("don't know how to decode LevelDB subchunk format version $subChunkVersion");
                        }
					}

				    if(($maps2d = $this->db->get($index . self::TAG_DATA_2D)) !== false){
						$binaryStream->setBuffer($maps2d, 0);

						$heightMap = array_values(unpack("v*", $binaryStream->get(512)));
						$biomeIds = $binaryStream->get(256);
					}
                    break;
                case 2:
					$legacyTerrain = $this->db->get($index . self::TAG_LEGACY_TERRAIN);
					if($legacyTerrain === false){
						throw new CorruptedChunkException("Expected to find a LEGACY_TERRAIN key for this chunk version, but none found");
					}
					$binaryStream->setBuffer($legacyTerrain);
                    $fullIds = $binaryStream->get(32768);
                    $fullData = $binaryStream->get(16384);
                    $fullSkyLight = $binaryStream->get(16384);
                    $fullBlockLight = $binaryStream->get(16384);

                    for($yy = 0; $yy < 8; ++$yy){
                        $subOffset = ($yy << 4);
                        $ids = "";
                        for($i = 0; $i < 256; ++$i){
                            $ids .= substr($fullIds, $subOffset, 16);
                            $subOffset += 128;
                        }
                        $data = "";
                        $subOffset = ($yy << 3);
                        for($i = 0; $i < 256; ++$i){
                            $data .= substr($fullData, $subOffset, 8);
                            $subOffset += 64;
                        }
                        $skyLight = "";
                        $subOffset = ($yy << 3);
                        for($i = 0; $i < 256; ++$i){
                            $skyLight .= substr($fullSkyLight, $subOffset, 8);
                            $subOffset += 64;
                        }
                        $blockLight = "";
                        $subOffset = ($yy << 3);
                        for($i = 0; $i < 256; ++$i){
                            $blockLight .= substr($fullBlockLight, $subOffset, 8);
                            $subOffset += 64;
                        }
                        $subChunks[$yy] = new SubChunk($ids, $data, $skyLight, $blockLight);
                    }

                    $heightMap = array_values(unpack("C*", $binaryStream->get(256)));
                    $biomeIds = ChunkUtils::convertBiomeColors(array_values(unpack("N*", $binaryStream->get(1024))));
                    break;
                default:
                    throw new UnsupportedChunkFormatException("don't know how to decode chunk format version $chunkVersion");
			}

			$nbt = new NBT(NBT::LITTLE_ENDIAN);

			$entities = [];
			if(($entityData = $this->db->get($index . self::TAG_ENTITY)) !== false and strlen($entityData) > 0){
				$nbt->read($entityData, true);
				$entities = $nbt->getData();
				if(!is_array($entities)){
					$entities = [$entities];
				}
			}

			foreach($entities as $entityNBT){
				if($entityNBT->id instanceof IntTag){
					$entityNBT["id"] &= 0xff;
				}
			}

			$tiles = [];
			if(($tileData = $this->db->get($index . self::TAG_BLOCK_ENTITY)) !== false and strlen($tileData) > 0){
				$nbt->read($tileData, true);
				$tiles = $nbt->getData();
				if(!is_array($tiles)){
					$tiles = [$tiles];
				}
			}

			$extraData = [];
			if(($extraRawData = $this->db->get($index . self::TAG_BLOCK_EXTRA_DATA)) !== false and strlen($extraRawData) > 0){
				$binaryStream->setBuffer($extraRawData, 0);
				$count = $binaryStream->getLInt();
				for($i = 0; $i < $count; ++$i){
					$key = $binaryStream->getLInt();
					$value = $binaryStream->getLShort();
					$extraData[$key] = $value;
				}
			}

			$chunk = new Chunk(
				$chunkX,
				$chunkZ,
				$subChunks,
				$entities,
				$tiles,
				$biomeIds,
                $heightMap,
                $extraData
			);


			$chunk->setGenerated(true);
			$finalisationChr = $this->db->get($index . self::TAG_STATE_FINALISATION);
			if($finalisationChr !== false){
				$finalisation = ord($finalisationChr);
				$chunk->setPopulated($finalisation === self::FINALISATION_DONE);
			}else{
				$chunk->setPopulated();
			}
			$chunk->setLightPopulated($lightPopulated);
			$chunk->setChanged($hasBeenUpgraded);

			return $chunk;
        }catch(UnsupportedChunkFormatException $e){

            $logger = MainLogger::getLogger();
            $logger->error("Failed to decode LevelDB chunk: " . $e->getMessage());

            return null;
		}catch(\Throwable $t){
			$logger = MainLogger::getLogger();
			$logger->error("LevelDB chunk decode error");
			$logger->logException($t);

			return null;

		}
	}
	protected function writeChunk(Chunk $chunk) : void{
		$index = LevelDB::chunkIndex($chunk->getX(), $chunk->getZ());

		$write = new \LevelDBWriteBatch();
		$write->put($index . self::TAG_VERSION, chr(self::CURRENT_LEVEL_CHUNK_VERSION));

		$subChunks = $chunk->getSubChunks();
        foreach($subChunks as $y => $subChunk){
            $key = $index . self::TAG_SUBCHUNK_PREFIX . chr($y);
            if($subChunk->isEmpty(false)){
				$write->delete($key);
            }else{
				$write->put($key,
                    chr(self::CURRENT_LEVEL_SUBCHUNK_VERSION) .
                    $subChunk->getBlockIdArray() .
                    $subChunk->getBlockDataArray()
                );
            }
		}

		$write->put($index . self::TAG_DATA_2D, pack("v*", ...$chunk->getHeightMapArray()) . $chunk->getBiomeIdArray());

        $extraData = $chunk->getBlockExtraDataArray();
        if(count($extraData) > 0){
            $stream = new BinaryStream();
            $stream->putLInt(count($extraData));
            foreach($extraData as $key => $value){
                $stream->putLInt($key);
                $stream->putLShort($value);
            }

			$write->put($index . self::TAG_BLOCK_EXTRA_DATA, $stream->getBuffer());
        }else{
			$write->delete($index . self::TAG_BLOCK_EXTRA_DATA);
        }

		$write->put($index . self::TAG_STATE_FINALISATION, chr($chunk->isPopulated() ? self::FINALISATION_DONE : self::FINALISATION_NEEDS_POPULATION));
		$tiles = [];
		foreach($chunk->getTiles() as $tile){
			if(!$tile->isClosed()){
				$tile->saveNBT();
				$tiles[] = $tile->namedtag;
			}
		}
		$this->writeTags($tiles, $index . self::TAG_BLOCK_ENTITY, $write);
		$entities = [];
		foreach($chunk->getSavableEntities() as $entity){
			$entity->saveNBT();
			$entities[] = $entity->namedtag;
		}
		$this->writeTags($entities, $index . self::TAG_ENTITY, $write);

		$write->delete($index . self::TAG_DATA_2D_LEGACY);
		$write->delete($index . self::TAG_LEGACY_TERRAIN);

		$this->db->write($write);
	}
	private function writeTags(array $targets, string $index, \LevelDBWriteBatch $write){
		if(!empty($targets)){
			$nbt = new NBT(NBT::LITTLE_ENDIAN);
			$nbt->setData($targets);
			$write->put($index, $nbt->write());
		}else{
			$write->delete($index);
		}
	}
	public function getDatabase() : \LevelDB{
		return $this->db;
	}
	public static function chunkIndex(int $chunkX, int $chunkZ) : string{
		return Binary::writeLInt($chunkX) . Binary::writeLInt($chunkZ);
	}

	public function close(){
		unset($this->db);
	}
}