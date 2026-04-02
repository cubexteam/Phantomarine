<?php

/*
 * Phantomarine Core
 * @author SantianDev
 */

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


class StartGamePacket extends DataPacket{

	const NETWORK_ID = ProtocolInfo::START_GAME_PACKET;

	public $entityUniqueId;
	public $entityRuntimeId;
	public $playerGamemode;
	public $x;
	public $y;
	public $z;
	public $pitch;
	public $yaw;
	public $seed;
	public $dimension;
	public $generator = 1;
	public $worldGamemode;
	public $difficulty;
	public $spawnX;
	public $spawnY;
	public $spawnZ;
	public $hasAchievementsDisabled = true;
	public $dayCycleStopTime = -1;
	public $eduMode = false;
	public $rainLevel;
	public $lightningLevel;
	public $commandsEnabled;
	public $gameRules = [];
	public $isTexturePacksRequired = true;
	public $levelId = "";
	public $worldName;
	public $premiumWorldTemplateId = "";
	public $unknownBool = false;
	public $currentTick = 0;
	public function decode(){

	}
	public function encode(){
		$this->reset();
		$this->putEntityId($this->entityUniqueId);
		$this->putEntityId($this->entityRuntimeId);
		$this->putVarInt($this->playerGamemode);
		$this->putVector3f($this->x, $this->y, $this->z);
		$this->putLFloat($this->pitch);
		$this->putLFloat($this->yaw);
		$this->putVarInt($this->seed);
		$this->putVarInt($this->dimension);
		$this->putVarInt($this->generator);
		$this->putVarInt($this->worldGamemode);
		$this->putVarInt($this->difficulty);
		$this->putBlockCoords($this->spawnX, $this->spawnY, $this->spawnZ);
		$this->putBool($this->hasAchievementsDisabled);
		$this->putVarInt($this->dayCycleStopTime);
		$this->putBool($this->eduMode);
		$this->putLFloat($this->rainLevel);
		$this->putLFloat($this->lightningLevel);
		$this->putBool($this->commandsEnabled);
		$this->putBool($this->isTexturePacksRequired);
		$this->putGameRules($this->gameRules);
		$this->putString($this->levelId);
		$this->putString($this->worldName);
		$this->putString($this->premiumWorldTemplateId);
		$this->putBool($this->unknownBool);
		$this->putLLong($this->currentTick);
	}

}
