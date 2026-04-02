<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\level\light;

use pocketmine\block\BlockFactory;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class LightPopulationTask extends AsyncTask{
	public $levelId;
	public $chunk;

	public function __construct(Level $level, Chunk $chunk){
		$this->levelId = $level->getId();
		$chunk->setLightPopulated(null);
		$this->chunk = $chunk->fastSerialize();
	}

	public function onRun(){
		if(!BlockFactory::isInit()){
			BlockFactory::init();
		}
		$chunk = Chunk::fastDeserialize($this->chunk);

		$chunk->recalculateHeightMap();
		$chunk->populateSkyLight();
		$chunk->setLightPopulated();

		$this->chunk = $chunk->fastSerialize();
	}

	public function onCompletion(Server $server){
		$level = $server->getLevel($this->levelId);
		if($level !== null){
			$chunk = Chunk::fastDeserialize($this->chunk);
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}
	}
}
