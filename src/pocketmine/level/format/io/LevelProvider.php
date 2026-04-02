<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */



namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;

interface LevelProvider{
	public function __construct(string $path);
	public static function getProviderName() : string;
	public function getWorldHeight() : int;
	public function getPath() : string;
	public static function isValid(string $path) : bool;
	public static function generate(string $path, string $name, int $seed, string $generator, array $options = []);
	public function getGenerator() : string;
	public function getGeneratorOptions() : array;
	public function saveChunk(Chunk $chunk) : void;
	public function loadChunk(int $chunkX, int $chunkZ, bool $create = false) : ?Chunk;
	public function getName();
	public function getTime();
	public function setTime($value);
	public function getSeed();
	public function setSeed($value);
	public function getSpawn() : Vector3;
	public function setSpawn(Vector3 $pos);
	public function getDifficulty() : int;
	public function setDifficulty(int $difficulty);
	public function doGarbageCollection();
	public function close();

}