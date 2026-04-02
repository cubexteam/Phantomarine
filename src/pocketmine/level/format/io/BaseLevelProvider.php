<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\level\LevelException;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\StringTag;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function mkdir;

abstract class BaseLevelProvider implements LevelProvider{
	protected $path;
	protected $levelData;

	public function __construct(string $path){
		$this->path = $path;
		if(!file_exists($this->path)){
			mkdir($this->path, 0777, true);
		}

		$this->loadLevelData();
		$this->fixLevelData();
	}

	protected function loadLevelData() : void{
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->readCompressed(file_get_contents($this->getPath() . "level.dat"));
		$levelData = $nbt->getData();
		if($levelData->Data instanceof CompoundTag){
			$this->levelData = $levelData->Data;
		}else{
			throw new LevelException("Invalid level.dat");
		}

	}

	protected function fixLevelData() : void{
		if(!isset($this->levelData->generatorName)){
			$this->levelData->generatorName = new StringTag("generatorName", "default");
		}elseif(($generatorName = self::hackyFixForGeneratorClasspathInLevelDat($this->levelData->generatorName)) !== null){
			$this->levelData->generatorName = new StringTag("generatorName", (string) $generatorName);
		}

		if(!isset($this->levelData->generatorOptions)){
			$this->levelData->generatorOptions = new StringTag("generatorOptions", "");
		}
	}
	protected static function hackyFixForGeneratorClasspathInLevelDat(string $className) : ?string{
		switch($className){
			case 'pocketmine\level\generator\normal\Normal':
				return "normal";
			case 'pocketmine\level\generator\Flat':
				return "flat";
		}

		return null;
	}
	public function getPath() : string{
		return $this->path;
	}
	public function getName() : string{
		return (string) $this->levelData["LevelName"];
	}
	public function getTime(){
		return $this->levelData["Time"];
	}
	public function setTime($value){
		$this->levelData->Time = new LongTag("Time", $value);
	}
	public function getSeed(){
		return $this->levelData["RandomSeed"];
	}
	public function setSeed($value){
		$this->levelData->RandomSeed = new LongTag("RandomSeed", (int) $value);
	}
	public function getSpawn() : Vector3{
		return new Vector3((float) $this->levelData["SpawnX"], (float) $this->levelData["SpawnY"], (float) $this->levelData["SpawnZ"]);
	}

	public function setSpawn(Vector3 $pos){
		$this->levelData->SpawnX = new IntTag("SpawnX", $pos->getFloorX());
		$this->levelData->SpawnY = new IntTag("SpawnY", $pos->getFloorY());
		$this->levelData->SpawnZ = new IntTag("SpawnZ", $pos->getFloorZ());
	}

	public function doGarbageCollection(){

	}
	public function getLevelData() : CompoundTag{
		return $this->levelData;
	}

	public function saveLevelData(){
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->setData(new CompoundTag("", [
			"Data" => $this->levelData
		]));
		$buffer = $nbt->writeCompressed();
		file_put_contents($this->getPath() . "level.dat", $buffer);
	}

	public function loadChunk(int $chunkX, int $chunkZ, bool $create = false) : ?Chunk{
		$chunk = $this->readChunk($chunkX, $chunkZ);
		if($chunk === null and $create){
			$chunk = new Chunk($chunkX, $chunkZ);
		}

		return $chunk;
	}

	public function saveChunk(Chunk $chunk) : void{
		if(!$chunk->isGenerated()){
			throw new \InvalidStateException("Cannot save un-generated chunk");
		}
		$this->writeChunk($chunk);
	}

	abstract protected function readChunk(int $chunkX, int $chunkZ) : ?Chunk;

	abstract protected function writeChunk(Chunk $chunk) : void;
}
