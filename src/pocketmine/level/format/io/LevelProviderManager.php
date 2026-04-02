<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\format\io;

use pocketmine\level\format\io\leveldb\LevelDB;
use pocketmine\level\format\io\region\Anvil;
use pocketmine\level\format\io\region\McRegion;
use pocketmine\level\format\io\region\PMAnvil;
use pocketmine\level\LevelException;

abstract class LevelProviderManager{
	protected static $providers = [];

	public static function init() : void{
		self::addProvider(Anvil::class);
		self::addProvider(McRegion::class);
		self::addProvider(PMAnvil::class);
		self::addProvider(LevelDB::class);
	}
	public static function addProvider(string $class){
		if(!is_subclass_of($class, LevelProvider::class)){
			throw new LevelException("Class is not a subclass of LevelProvider");
		}
		self::$providers[strtolower($class::getProviderName())] = $class;
	}
	public static function getProvider(string $path){
		foreach(self::$providers as $provider){
			if($provider::isValid($path)){
				return $provider;
			}
		}

		return null;
	}
	public static function getProviderByName(string $name){
		return self::$providers[trim(strtolower($name))] ?? null;
	}
}