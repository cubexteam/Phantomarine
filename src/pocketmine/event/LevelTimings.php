<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;

use pocketmine\level\Level;

class LevelTimings{
	public $setBlock;
	public $doBlockLightUpdates;
	public $doBlockSkyLightUpdates;
	public $doChunkUnload;
	public $doTickPending;
	public $doTickTiles;
	public $doChunkGC;
	public $entityTick;
	public $tileEntityTick;
	public $doTick;
	public $syncChunkSendTimer;
	public $syncChunkSendPrepareTimer;
	public $syncChunkLoadTimer;
	public $syncChunkLoadDataTimer;
	public $syncChunkLoadEntitiesTimer;
	public $syncChunkLoadTileEntitiesTimer;
	public $syncChunkSaveTimer;
	public function __construct(Level $level){
		$name = $level->getFolderName() . " - ";

		$this->setBlock = new TimingsHandler("** " . $name . "setBlock");
		$this->doBlockLightUpdates = new TimingsHandler("** " . $name . "doBlockLightUpdates");
		$this->doBlockSkyLightUpdates = new TimingsHandler("** " . $name . "doBlockSkyLightUpdates");

		$this->doChunkUnload = new TimingsHandler("** " . $name . "doChunkUnload");
		$this->doTickPending = new TimingsHandler("** " . $name . "doTickPending");
		$this->doTickTiles = new TimingsHandler("** " . $name . "doTickTiles");
		$this->doChunkGC = new TimingsHandler("** " . $name . "doChunkGC");
		$this->entityTick = new TimingsHandler("** " . $name . "entityTick");
		$this->tileEntityTick = new TimingsHandler("** " . $name . "tileEntityTick");

		Timings::init();
		$this->syncChunkSendTimer = new TimingsHandler("** " . $name . "syncChunkSend", Timings::$playerChunkSendTimer);
		$this->syncChunkSendPrepareTimer = new TimingsHandler("** " . $name . "syncChunkSendPrepare", Timings::$playerChunkSendTimer);

		$this->syncChunkLoadTimer = new TimingsHandler("** " . $name . "syncChunkLoad", Timings::$worldLoadTimer);
		$this->syncChunkLoadDataTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Data");
		$this->syncChunkLoadEntitiesTimer = new TimingsHandler("** " . $name . "syncChunkLoad - Entities");
		$this->syncChunkLoadTileEntitiesTimer = new TimingsHandler("** " . $name . "syncChunkLoad - TileEntities");
		$this->syncChunkSaveTimer = new TimingsHandler("** " . $name . "syncChunkSave", Timings::$worldSaveTimer);

		$this->doTick = new TimingsHandler($name . "doTick");
	}

}