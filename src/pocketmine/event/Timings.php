<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\event;

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\scheduler\TaskHandler;
use pocketmine\tile\Tile;
use function dechex;

abstract class Timings{
	private static $initialized = false;
	public static $fullTickTimer;
	public static $serverTickTimer;
	public static $memoryManagerTimer;
	public static $garbageCollectorTimer;
	public static $titleTickTimer;
	public static $serverRawPacketTimer;
	public static $playerNetworkTimer;
	public static $playerNetworkReceiveTimer;
	public static $playerChunkOrderTimer;
	public static $playerChunkSendTimer;
	public static $connectionTimer;
	public static $schedulerTimer;
	public static $serverCommandTimer;
	public static $worldLoadTimer;
	public static $worldSaveTimer;
	public static $populationTimer;
	public static $generationCallbackTimer;
	public static $permissibleCalculationTimer;
	public static $permissionDefaultTimer;
	public static $entityMoveTimer;
	public static $playerCheckNearEntitiesTimer;
	public static $tickEntityTimer;
	public static $tickTileEntityTimer;
	public static $timerEntityBaseTick;
	public static $timerLivingEntityBaseTick;
	public static $schedulerSyncTimer;
	public static $schedulerAsyncTimer;
	public static $playerCommandTimer;
	public static $craftingDataCacheRebuildTimer;
	public static $entityTypeTimingMap = [];
	public static $tileEntityTypeTimingMap = [];
	public static $packetReceiveTimingMap = [];
	public static $packetSendTimingMap = [];
	public static $pluginTaskTimingMap = [];

	public static function init(){
		if(self::$initialized){
			return;
		}
		self::$initialized = true;

		self::$fullTickTimer = new TimingsHandler("Full Server Tick");
		self::$serverTickTimer = new TimingsHandler("** Full Server Tick", self::$fullTickTimer);
		self::$memoryManagerTimer = new TimingsHandler("Memory Manager");
		self::$garbageCollectorTimer = new TimingsHandler("Garbage Collector", self::$memoryManagerTimer);
		self::$titleTickTimer = new TimingsHandler("Console Title Tick");
		self::$serverRawPacketTimer = new TimingsHandler("Raw packets (Query)");
		self::$playerNetworkTimer = new TimingsHandler("Player Network Send");
		self::$playerNetworkReceiveTimer = new TimingsHandler("Player Network Receive");
		self::$playerChunkOrderTimer = new TimingsHandler("Player Order Chunks");
		self::$playerChunkSendTimer = new TimingsHandler("Player Send Chunks");
		self::$connectionTimer = new TimingsHandler("Connection Handler");
		self::$schedulerTimer = new TimingsHandler("Scheduler");
		self::$serverCommandTimer = new TimingsHandler("Server Command");
		self::$worldLoadTimer = new TimingsHandler("World Load");
		self::$worldSaveTimer = new TimingsHandler("World Save");
		self::$populationTimer = new TimingsHandler("World Population");
		self::$generationCallbackTimer = new TimingsHandler("World Generation Callback");
		self::$permissibleCalculationTimer = new TimingsHandler("Permissible Calculation");
		self::$permissionDefaultTimer = new TimingsHandler("Default Permission Calculation");

		self::$entityMoveTimer = new TimingsHandler("** entityMove");
		self::$playerCheckNearEntitiesTimer = new TimingsHandler("** checkNearEntities");
		self::$tickEntityTimer = new TimingsHandler("** tickEntity");
		self::$tickTileEntityTimer = new TimingsHandler("** tickTileEntity");

		self::$timerEntityBaseTick = new TimingsHandler("** entityBaseTick");
		self::$timerLivingEntityBaseTick = new TimingsHandler("** livingEntityBaseTick");

		self::$schedulerSyncTimer = new TimingsHandler("** Scheduler - Sync Tasks");
		self::$schedulerAsyncTimer = new TimingsHandler("** Scheduler - Async Tasks");

		self::$playerCommandTimer = new TimingsHandler("** playerCommand");
		self::$craftingDataCacheRebuildTimer = new TimingsHandler("** craftingDataCacheRebuild");

	}
	public static function getScheduledTaskTimings(TaskHandler $task, int $period) : TimingsHandler{
		$name = "Task: " . ($task->getOwnerName() ?? "Unknown") . " Runnable: " . $task->getTaskName();

		if($period > 0){
			$name .= "(interval:" . $period . ")";
		}else{
			$name .= "(Single)";
		}

		if(!isset(self::$pluginTaskTimingMap[$name])){
			self::$pluginTaskTimingMap[$name] = new TimingsHandler($name, self::$schedulerSyncTimer);
		}

		return self::$pluginTaskTimingMap[$name];
	}
	public static function getEntityTimings(Entity $entity){
		$entityType = (new \ReflectionClass($entity))->getShortName();
		if(!isset(self::$entityTypeTimingMap[$entityType])){
			if($entity instanceof Player){
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - EntityPlayer", self::$tickEntityTimer);
			}else{
				self::$entityTypeTimingMap[$entityType] = new TimingsHandler("** tickEntity - " . $entityType, self::$tickEntityTimer);
			}
		}

		return self::$entityTypeTimingMap[$entityType];
	}
	public static function getTileEntityTimings(Tile $tile){
		$tileType = (new \ReflectionClass($tile))->getShortName();
		if(!isset(self::$tileEntityTypeTimingMap[$tileType])){
			self::$tileEntityTypeTimingMap[$tileType] = new TimingsHandler("** tickTileEntity - " . $tileType, self::$tickTileEntityTimer);
		}

		return self::$tileEntityTypeTimingMap[$tileType];
	}
	public static function getReceiveDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetReceiveTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetReceiveTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** receivePacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkReceiveTimer);
		}

		return self::$packetReceiveTimingMap[$pk::NETWORK_ID];
	}
	public static function getSendDataPacketTimings(DataPacket $pk){
		if(!isset(self::$packetSendTimingMap[$pk::NETWORK_ID])){
			$pkName = (new \ReflectionClass($pk))->getShortName();
			self::$packetSendTimingMap[$pk::NETWORK_ID] = new TimingsHandler("** sendPacket - " . $pkName . " [0x" . dechex($pk::NETWORK_ID) . "]", self::$playerNetworkTimer);
		}

		return self::$packetSendTimingMap[$pk::NETWORK_ID];
	}

}