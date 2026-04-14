<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator;

use pocketmine\block\BlockFactory;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\Level;
use pocketmine\level\SimpleChunkManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Random;
use function serialize;
use function unserialize;

class GeneratorRegisterTask extends AsyncTask{
	public $generatorClass;
	public $settings;
	public $seed;
	public $levelId;
	public $waterHeight;
	public $worldHeight = Level::Y_MAX;
	public function __construct(Level $level, string $generatorClass, array $generatorSettings = []){
		$this->generatorClass = $generatorClass;
		$this->waterHeight = $level->getWaterHeight();
		$this->settings = serialize($generatorSettings);
		$this->seed = $level->getSeed();
		$this->levelId = $level->getId();
		$this->worldHeight = $level->getWorldHeight();
	}

	public function onRun(){
		BlockFactory::init();
		Biome::init();
		$manager = new SimpleChunkManager($this->seed, $this->waterHeight, $this->worldHeight);
		$this->worker->saveToThreadStore("generation.level{$this->levelId}.manager", $manager);
		$generator = new $this->generatorClass(unserialize($this->settings, ["allowed_classes" => false]));
		$generator->init($manager, new Random($manager->getSeed()));
		$this->worker->saveToThreadStore("generation.level{$this->levelId}.generator", $generator);
	}
}
