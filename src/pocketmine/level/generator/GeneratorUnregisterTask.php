<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\generator;

use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;

class GeneratorUnregisterTask extends AsyncTask{
	public $levelId;

	public function __construct(Level $level){
		$this->levelId = $level->getId();
	}

	public function onRun(){
		$this->worker->removeFromThreadStore("generation.level{$this->levelId}.manager");
		$this->worker->removeFromThreadStore("generation.level{$this->levelId}.generator");
	}
}