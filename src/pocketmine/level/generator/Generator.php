<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator;

use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\utils\Utils;
use function preg_match;

abstract class Generator{
	public static function convertSeed(string $seed) : ?int{
		if($seed === ""){
			$convertedSeed = null;
		}elseif(preg_match('/^-?\d+$/', $seed) === 1){
			$convertedSeed = (int) $seed;
		}else{
			$convertedSeed = Utils::javaStringHash($seed);
		}

		return $convertedSeed;
	}
	public function getWaterHeight() : int{
		return 0;
	}
	protected $level;
	protected $random;
	public abstract function __construct(array $settings = []);

	public function init(ChunkManager $level, Random $random){
		$this->level = $level;
		$this->random = $random;
	}
	public abstract function generateChunk($chunkX, $chunkZ);
	public abstract function populateChunk($chunkX, $chunkZ);

	public abstract function getSettings();

	public abstract function getName();

	public abstract function getSpawn();
}
